<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseCategory;
use OpenAI\Client;

class AiCategorizationService
{
    protected Client $client;

    public function __construct()
    {
        $this->client = \OpenAI::client(config('services.openai.api_key'));
    }

    /**
     * Categorize uncategorized purchases using AI
     * 
     * @return array ['success' => int, 'failed' => int, 'errors' => array]
     */
    public function categorizeUncategorizedPurchases(): array
    {
        // Get distinct suppliers from uncategorized purchases
        $suppliers = Purchase::whereNull('purchase_category_id')
            ->pluck('supplier')
            ->unique()
            ->filter()
            ->values()
            ->toArray();

        if (empty($suppliers)) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['No uncategorized purchases found.']
            ];
        }

        // Get all available categories
        $categories = PurchaseCategory::pluck('name')->toArray();

        if (empty($categories)) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['No categories available. Please create categories first.']
            ];
        }

        // Build the prompt
        $prompt = $this->buildPrompt($suppliers, $categories);

        try {
            // Call OpenAI API
            $response = $this->client->chat()->create([
                'model' => config('services.openai.model', 'gpt-4-turbo-preview'),
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a helpful assistant that categorizes suppliers into expense categories. Always respond with valid JSON only, no additional text.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'response_format' => ['type' => 'json_object']
            ]);

            $content = $response->choices[0]->message->content;
            $categorizations = json_decode($content, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                return [
                    'success' => 0,
                    'failed' => 0,
                    'errors' => ['Failed to parse AI response: ' . json_last_error_msg()]
                ];
            }

            // Extract the categorizations array from the response
            $mappings = $categorizations['categorizations'] ?? $categorizations;

            if (!is_array($mappings)) {
                return [
                    'success' => 0,
                    'failed' => 0,
                    'errors' => ['Invalid AI response format']
                ];
            }

            // Apply categorizations
            return $this->applyCategorizations($mappings);

        } catch (\Exception $e) {
            return [
                'success' => 0,
                'failed' => 0,
                'errors' => ['API Error: ' . $e->getMessage()]
            ];
        }
    }

    /**
     * Build the categorization prompt
     */
    protected function buildPrompt(array $suppliers, array $categories): string
    {
        $suppliersList = implode("\n", array_map(fn($s, $i) => ($i + 1) . ". " . $s, $suppliers, array_keys($suppliers)));
        $categoriesList = implode("\n", array_map(fn($c, $i) => ($i + 1) . ". " . $c, $categories, array_keys($categories)));

        return <<<PROMPT
I will provide you with two lists:
1. A list of suppliers.
2. A list of available expense categories.

Your task is to assign exactly one category from the list of available categories to each supplier.
Always choose the most appropriate and specific category based on the supplier's name and the type of service they likely provide.

The output must be a valid JSON object with a "categorizations" array.
Each element must be an object in the following format:
{
  "supplier": "<SUPPLIER NAME>",
  "category": "<ASSIGNED CATEGORY>"
}

Do not invent new categories; only use the categories I provide.

Here are the suppliers:
$suppliersList

Here are the available categories:
$categoriesList

Respond ONLY with the JSON object, no additional text or explanation.
PROMPT;
    }

    /**
     * Apply the categorizations to purchases
     */
    protected function applyCategorizations(array $mappings): array
    {
        $success = 0;
        $failed = 0;
        $errors = [];

        // Build a category name => id map
        $categoryMap = PurchaseCategory::pluck('id', 'name')->toArray();

        foreach ($mappings as $mapping) {
            if (!isset($mapping['supplier']) || !isset($mapping['category'])) {
                $failed++;
                $errors[] = 'Invalid mapping format: ' . json_encode($mapping);
                continue;
            }

            $supplier = $mapping['supplier'];
            $categoryName = $mapping['category'];

            // Check if category exists
            if (!isset($categoryMap[$categoryName])) {
                $failed++;
                $errors[] = "Category '{$categoryName}' not found for supplier '{$supplier}'";
                continue;
            }

            $categoryId = $categoryMap[$categoryName];

            // Update all purchases with this supplier that don't have a category
            $updated = Purchase::whereNull('purchase_category_id')
                ->where('supplier', $supplier)
                ->update(['purchase_category_id' => $categoryId]);

            if ($updated > 0) {
                $success += $updated;
            } else {
                $failed++;
                $errors[] = "No uncategorized purchases found for supplier '{$supplier}'";
            }
        }

        return [
            'success' => $success,
            'failed' => $failed,
            'errors' => $errors
        ];
    }
}
