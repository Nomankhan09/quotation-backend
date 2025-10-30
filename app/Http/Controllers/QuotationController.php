<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationPaymentTerm;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class QuotationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 5);
            $page = $request->get('page', 1);
            $search = $request->get('search', '');
            $query = Quotation::with([
                'lead:id,full_name,email,phone,company_name',
                'products.product:id,product_name,description,unit_price',
                'paymentTerms'
            ])
            ->where('user_id', auth()->id())
            ->orderBy('created_at', 'desc');

            // Add search functionality
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                      ->orWhereHas('lead', function ($leadQuery) use ($search) {
                          $leadQuery->where('full_name', 'like', "%{$search}%")
                                   ->orWhere('company_name', 'like', "%{$search}%")
                                   ->orWhere('phone', 'like', "%{$search}%")
                                   ->orWhere('email', 'like', "%{$search}%");
                      })
                      ->orWhereHas('products.product', function ($productQuery) use ($search) {
                          $productQuery->where('product_name', 'like', "%{$search}%");
                      });
                });
            }

            $paginator = $query->paginate($perPage, ['*'], 'page', $page);
            
            $formattedQuotations = $paginator->getCollection()->map(function ($quotation) {
                return [
                    'id' => $quotation->id,
                    'quotationNumber' => $quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 4, '0', STR_PAD_LEFT),
                    'leadId' => $quotation->lead_id,
                    'productIds' => $quotation->products->pluck('product_id')->toArray(),
                    'totalAmount' => (float) $quotation->total_amount,
                    'status' => $quotation->status ?? 'draft',
                    'validUntil' => $quotation->valid_until ?? now()->addDays(30)->format('Y-m-d'),
                    'created_at' => $quotation->created_at->toISOString(),
                    'notes' => $this->generateNotesFromQuotation($quotation),
                    'leadName' => $quotation->lead->full_name ?? 'Unknown Lead',
                    'products' => $quotation->products->map(function ($quotationProduct) {
                        return [
                            'productId' => $quotationProduct->product_id,
                            'productName' => $quotationProduct->product->product_name ?? 'Unknown Product',
                            'quantity' => $quotationProduct->quantity,
                            'unitPrice' => (float) $quotationProduct->unit_price,
                            'totalPrice' => (float) $quotationProduct->total_price,
                        ];
                    }),
                    'paymentTerms' => $quotation->paymentTerms->map(function ($term) {
                        return [
                            'description' => $term->description,
                            'value' => $term->value,
                        ];
                    }),
                ];
            });
            \Log::info("Fetched " . count($formattedQuotations) . " quotations");
            return response()->json([
                'message' => 'Quotations retrieved successfully',
                'quotations' => $formattedQuotations,
                'pagination' => [
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                    'per_page' => $paginator->perPage(),
                    'total' => $paginator->total(),
                    'has_more_pages' => $paginator->hasMorePages(),
                ]
            ], 200);

        } catch (\Throwable $th) {
            \Log::error("Error fetching quotations: " . $th->getMessage());
            return response()->json([
                'error' => 'Failed to retrieve quotations',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Get quotation by ID
     */
    public function show($id): JsonResponse
    {
        try {
            $quotation = Quotation::with([
                'lead:id,full_name,email,phone,company_name',
                'products.product:id,product_name,description,unit_price,category_id',
                'paymentTerms'
            ])
            ->where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

            $formattedQuotation = [
                'id' => $quotation->id,
                'quotationNumber' => $quotation->quotation_number ?? 'QUO-' . str_pad($quotation->id, 4, '0', STR_PAD_LEFT),
                'leadId' => $quotation->lead_id,
                'subtotal' => (float) $quotation->subtotal,
                'discount' => [
                    'type' => $quotation->discount_type,
                    'value' => (float) $quotation->discount_value,
                ],
                'discountAmount' => (float) $quotation->discount_amount,
                'totalAmount' => (float) $quotation->total_amount,
                'status' => $quotation->status ?? 'draft',
                'validUntil' => $quotation->valid_until ?? now()->addDays(30)->format('Y-m-d'),
                'created_at' => $quotation->created_at->toISOString(),
                'notes' => $quotation->notes ?? $this->generateNotesFromQuotation($quotation),
                'terms' => $quotation->terms ?? [],
                'lead' => [
                    'id' => $quotation->lead->id,
                    'full_name' => $quotation->lead->full_name,
                    'company_name' => $quotation->lead->company_name,
                    'email' => $quotation->lead->email,
                    'phone' => $quotation->lead->phone,
                ],
                'products' => $quotation->products->map(function ($quotationProduct) {
                    return [
                        'productId' => $quotationProduct->product_id,
                        'product_name' => $quotationProduct->product->product_name ?? 'Unknown Product',
                        'categoryName' => $quotationProduct->product->category->category_name ?? 'Other',
                        'description' => $quotationProduct->product->description ?? '',
                        'unitPrice' => (float) $quotationProduct->unit_price,
                        'quantity' => $quotationProduct->quantity,
                        'length' => (float) $quotationProduct->length,
                        'width' => (float) $quotationProduct->width,
                        'unit' => $quotationProduct->unit,
                        'totalPrice' => (float) $quotationProduct->total_price,
                    ];
                }),
                'paymentTerms' => $quotation->paymentTerms->map(function ($term) {
                    return [
                        'id' => $term->id,
                        'description' => $term->description,
                        'value' => $term->value,
                    ];
                }),
            ];

            return response()->json([
                'message' => 'Quotation retrieved successfully',
                'quotation' => $formattedQuotation
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'error' => 'Failed to retrieve quotation',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new quotation
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'leadId' => 'required|integer|exists:leads,id',
            'subtotal' => 'required|numeric',
            'discount.type' => 'required|in:percentage,fixed,percent',
            'discount.value' => 'required|numeric',
            'discountAmount' => 'required|numeric',
            'totalAmount' => 'required|numeric',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected',
            'terms' => 'nullable|array',
            'paymentTerms' => 'nullable|array',
            'products' => 'required|array|min:1',
            'products.*.productId' => 'required|integer|exists:products,id',
            'products.*.unitPrice' => 'required|numeric',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.length' => 'nullable|numeric',
            'products.*.width' => 'nullable|numeric',
            'products.*.unit' => 'nullable|string|in:feet,inches',
            'products.*.totalPrice' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            // Normalize discount type
            $discountType = $data['discount']['type'] === 'percentage' ? 'percent' : $data['discount']['type'];

            $quotation = Quotation::create([
                'user_id' => auth()->id(),
                'lead_id' => $data['leadId'],
                'subtotal' => $data['subtotal'],
                'discount_type' => $discountType,
                'discount_value' => $data['discount']['value'],
                'discount_amount' => $data['discountAmount'],
                'total_amount' => $data['totalAmount'],
                'status' => $data['status'] ?? 'draft',
                'terms' => $data['terms'] ?? [],
                'valid_until' => now()->addDays(30), // Default 30 days validity
            ]);

            foreach ($data['products'] as $product) {
                QuotationProduct::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product['productId'],
                    'length' => $product['length'] ?? null,
                    'width' => $product['width'] ?? null,
                    'unit' => $product['unit'] ?? null,
                    'unit_price' => $product['unitPrice'],
                    'quantity' => $product['quantity'],
                    'total_price' => $product['totalPrice'],
                ]);
            }

            if (!empty($data['paymentTerms'])) {
                foreach ($data['paymentTerms'] as $term) {
                    QuotationPaymentTerm::create([
                        'quotation_id' => $quotation->id,
                        'description' => $term['description'],
                        'value' => $term['value'],
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Quotation saved successfully',
                'quotation' => $quotation->load(['products.product', 'paymentTerms', 'lead'])
            ], 201);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to save quotation',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an existing quotation
     */
    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'leadId' => 'required|integer|exists:leads,id',
            'subtotal' => 'required|numeric',
            'discount.type' => 'required|in:percentage,fixed,percent',
            'discount.value' => 'required|numeric',
            'discountAmount' => 'required|numeric',
            'totalAmount' => 'required|numeric',
            'status' => 'nullable|string|in:draft,sent,accepted,rejected',
            'terms' => 'nullable|array',
            'paymentTerms' => 'nullable|array',
            'products' => 'required|array|min:1',
            'products.*.productId' => 'required|integer|exists:products,id',
            'products.*.unitPrice' => 'required|numeric',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.length' => 'nullable|numeric',
            'products.*.width' => 'nullable|numeric',
            'products.*.unit' => 'nullable|string|in:feet,inches',
            'products.*.totalPrice' => 'required|numeric',
        ]);

        try {
            DB::beginTransaction();

            $quotation = Quotation::where('user_id', auth()->id())
                ->where('id', $id)
                ->firstOrFail();

            // Normalize discount type
            $discountType = $data['discount']['type'] === 'percentage' ? 'percent' : $data['discount']['type'];

            // Update quotation
            $quotation->update([
                'lead_id' => $data['leadId'],
                'subtotal' => $data['subtotal'],
                'discount_type' => $discountType,
                'discount_value' => $data['discount']['value'],
                'discount_amount' => $data['discountAmount'],
                'total_amount' => $data['totalAmount'],
                'status' => $data['status'] ?? $quotation->status,
                'terms' => $data['terms'] ?? $quotation->terms,
            ]);

            // Delete existing products and payment terms
            QuotationProduct::where('quotation_id', $quotation->id)->delete();
            QuotationPaymentTerm::where('quotation_id', $quotation->id)->delete();

            // Add new products
            foreach ($data['products'] as $product) {
                QuotationProduct::create([
                    'quotation_id' => $quotation->id,
                    'product_id' => $product['productId'],
                    'length' => $product['length'] ?? null,
                    'width' => $product['width'] ?? null,
                    'unit' => $product['unit'] ?? null,
                    'unit_price' => $product['unitPrice'],
                    'quantity' => $product['quantity'],
                    'total_price' => $product['totalPrice'],
                ]);
            }

            // Add new payment terms
            if (!empty($data['paymentTerms'])) {
                foreach ($data['paymentTerms'] as $term) {
                    QuotationPaymentTerm::create([
                        'quotation_id' => $quotation->id,
                        'description' => $term['description'],
                        'value' => $term['value'],
                    ]);
                }
            }

            DB::commit();

            // Reload the quotation with relationships
            $quotation->load(['products.product', 'paymentTerms', 'lead']);

            return response()->json([
                'message' => 'Quotation updated successfully',
                'quotation' => $quotation
            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            return response()->json([
                'error' => 'Failed to update quotation',
                'message' => $th->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate notes from quotation data
     */
    private function generateNotesFromQuotation(Quotation $quotation): string
    {
        $notes = [];

        // Add discount information
        if ($quotation->discount_value > 0) {
            $discountType = $quotation->discount_type === 'percent' ? '%' : ' fixed';
            $notes[] = "Discount: {$quotation->discount_value}{$discountType}";
        }

        // Add products count
        $productsCount = $quotation->products->count();
        $notes[] = "Products: {$productsCount} item" . ($productsCount > 1 ? 's' : '');

        // Add payment terms count if exists
        $paymentTermsCount = $quotation->paymentTerms->count();
        if ($paymentTermsCount > 0) {
            $notes[] = "Payment terms: {$paymentTermsCount}";
        }

        return implode(' • ', $notes);
    }
}