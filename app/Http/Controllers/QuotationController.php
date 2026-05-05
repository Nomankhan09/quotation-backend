<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\PaymentTerm;
use App\Models\Quotation;
use App\Models\QuotationProduct;
use App\Models\QuotationPaymentTerm;
use App\Models\Specification;
use App\Models\Term;
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

    public function getQuotationByStage()
    {
        $quotations = Quotation::with([
            'lead:id,full_name,email,phone,company_name',
        ])->where("user_id", auth()->id())
            ->whereNotNull("stage")
            ->get();

        $grouped = [
            'proposal' => $quotations->where('stage', 'Proposal')->values(),
            'negotiation' => $quotations->where('stage', 'Negotiation')->values(),
            'won' => $quotations->where('stage', 'Won')->values(),
        ];

        return response()->json([
            'message' => 'Quotation retrieved successfully',
            'data' => $grouped
        ], 200);
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
                'paymentTerms.paymentTerm',
            ])
                ->where('user_id', auth()->id())
                ->where('id', $id)
                ->firstOrFail();

            $specIds = is_array($quotation->specifications)
                ? $quotation->specifications
                : json_decode($quotation->specifications, true);

            $quotation->specification_data = Specification::whereIn(
                'id',
                $specIds ?? []
            )->get();

            $termIds = is_array($quotation->terms)
                ? $quotation->terms
                : json_decode($quotation->terms, true);

            $quotation->terms_data = Term::whereIn(
                'id',
                $termIds ?? []
            )->get();

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
                'paymentTerms' => $quotation->paymentTerms
                    ->filter(fn($term) => $term->paymentTerm)
                    ->map(function ($term) {

                        return [
                            'id' => $term->paymentTerm->id,
                            'quotation_payment_term_id' => $term->id,
                            'description' => $term->paymentTerm->description,
                            'value' => $term->paymentTerm->value,
                        ];
                    })
                    ->values(),
                'specifications' => $quotation->specification_data->map(function ($spec) {
                    return [
                        'id' => $spec->id,
                        'item' => $spec->item,
                        'description' => $spec->description,
                    ];
                }),
                'terms' => $quotation->terms_data->map(function ($term) {
                    return [
                        'id' => $term->id,
                        'text' => $term->text
                    ];
                })
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
            'specifications' => 'nullable|array',
            'specifications.*' => 'integer|exists:specifications,id',
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
                'specifications' => $data['specifications'] ?? [],
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
                foreach ($data['paymentTerms'] as $termId) {

                    $paymentTerm = PaymentTerm::find($termId);

                    if (!$paymentTerm) {
                        continue;
                    }

                    QuotationPaymentTerm::create([
                        'quotation_id' => $quotation->id,
                        'payment_term_id' => $paymentTerm->id,
                        'description' => $paymentTerm->description,
                        'value' => $paymentTerm->value,
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
            'specifications' => 'nullable|array',
            'specifications.*' => 'integer|exists:specifications,id',
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
                'specifications' => $data['specifications'] ?? $quotation->specifications,
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
                foreach ($data['paymentTerms'] as $termId) {

                    $paymentTerm = PaymentTerm::find($termId);

                    if (!$paymentTerm) {
                        continue;
                    }

                    QuotationPaymentTerm::create([
                        'quotation_id' => $quotation->id,
                        'payment_term_id' => $paymentTerm->id,
                        'description' => $paymentTerm->description,
                        'value' => $paymentTerm->value,
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

    public function updateStage(Request $request, $id)
    {
        $data = $request->validate([
            'stage' => 'required|string',
        ]);

        $quotation = Quotation::where('user_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $quotation->update([
            'stage' => $data['stage'],
        ]);

        $quotation->load('lead');

        return response()->json([
            'message' => 'Stage updated successfully',
            'quotation' => $quotation
        ]);
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
        $paymentTermsCount = count($quotation->paymentTerms ?? []);
        if ($paymentTermsCount > 0) {
            $notes[] = "Payment terms: {$paymentTermsCount}";
        }

        return implode(' • ', $notes);
    }

    public function getQuotationsByLead($leadId)
    {
        $quotations = Quotation::with(
                'lead',
                'products.product:id,product_name'
            )->where("lead_id", $leadId)->get();

        if (!$quotations) {
            return response()->json([
                'status' => 404,
                'message' => 'No Quotation found'
            ]);
        }

        return response()->json(['status' => 200, 'data' => $quotations]);
    }
}
