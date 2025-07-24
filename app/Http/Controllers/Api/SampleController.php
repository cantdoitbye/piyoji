<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BuyerAssignmentService;
use App\Services\SampleService;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SampleController extends Controller
{
    public function __construct(
        protected SampleService $sampleService,
        protected SellerService $sellerService,
        protected BuyerAssignmentService $buyerAssignmentService
    ) {}

    /**
     * Get list of samples for mobile app
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $filters = [
                'status' => $request->get('status'),
                'evaluation_status' => $request->get('evaluation_status'),
                'seller_id' => $request->get('seller_id'),
                'user_samples_only' => $request->get('user_samples_only', false),
                'per_page' => $request->get('per_page', 20)
            ];

            $samples = $this->sampleService->getSamplesForMobile(Auth::id(), $filters);

            return response()->json([
                'success' => true,
                'data' => [
                    'samples' => $samples->items(),
                    'pagination' => [
                        'current_page' => $samples->currentPage(),
                        'last_page' => $samples->lastPage(),
                        'per_page' => $samples->perPage(),
                        'total' => $samples->total(),
                        'has_more_pages' => $samples->hasMorePages()
                    ]
                ],
                'message' => 'Samples retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve samples: ' . $e->getMessage()
            ], 500);
        }
    }


      public function sellers()
    {
        try {
            $seller = $this->sellerService->index();

            if (!$seller) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sellers not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $seller,
                'message' => 'Sellers list retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sellers list: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample details
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $sample = $this->sampleService->getSampleDetailsForMobile($id);

            if (!$sample) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sample not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $sample,
                'message' => 'Sample details retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sample details: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create new sample via mobile app (Module 2.1)
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sample_name' => 'required|string|max:255',
            'seller_id' => 'required|exists:sellers,id',
            'batch_id' => 'required|string|max:255',
            'sample_weight' => 'nullable|numeric|min:0|max:999.99',
            'arrival_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sample = $this->sampleService->createSampleViaMobile(
                $validator->validated(), 
                Auth::id()
            );

            return response()->json([
                'success' => true,
                'data' => $sample,
                'message' => 'Sample created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create sample: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update sample via mobile app
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'sample_name' => 'sometimes|required|string|max:255',
            'seller_id' => 'sometimes|required|exists:sellers,id',
            'batch_id' => 'sometimes|required|string|max:255',
            'sample_weight' => 'nullable|numeric|min:0|max:999.99',
            'arrival_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $sample = $this->sampleService->updateSample($id, $validator->validated());

            return response()->json([
                'success' => true,
                'data' => $sample,
                'message' => 'Sample updated successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update sample: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Start sample evaluation (Module 2.2)
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function startEvaluation(int $id)
    {
        try {
            $sample = $this->sampleService->startEvaluation($id, Auth::id());

            return response()->json([
                'success' => true,
                'data' => $sample,
                'message' => 'Sample evaluation started successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to start evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Submit sample evaluation (Module 2.2)
     * 
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitEvaluation(Request $request, int $id)
    {
        $validator = Validator::make($request->all(), [
            'aroma_score' => 'required|numeric|min:0|max:10',
            'liquor_score' => 'required|numeric|min:0|max:10',
            'appearance_score' => 'required|numeric|min:0|max:10',
            'evaluation_comments' => 'nullable|string|max:1000'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $evaluationData = $validator->validated();
            $evaluationData['evaluated_by'] = Auth::id();

            $sample = $this->sampleService->saveEvaluation($id, $evaluationData);

            return response()->json([
                'success' => true,
                'data' => $sample,
                'message' => 'Sample evaluation submitted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit evaluation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pending evaluations for current user
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function pendingEvaluations()
    {
        try {
            $samples = $this->sampleService->getPendingEvaluationSamples();

            return response()->json([
                'success' => true,
                'data' => $samples,
                'message' => 'Pending evaluations retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending evaluations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get evaluated samples
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function evaluatedSamples()
    {
        try {
            $samples = $this->sampleService->getEvaluatedSamples();

            return response()->json([
                'success' => true,
                'data' => $samples,
                'message' => 'Evaluated samples retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve evaluated samples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get approved samples
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function approvedSamples()
    {
        try {
            $samples = $this->sampleService->getApprovedSamples();

            return response()->json([
                'success' => true,
                'data' => $samples,
                'message' => 'Approved samples retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approved samples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get top scoring samples
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function topScoringSamples(Request $request)
    {
        try {
            $minScore = $request->get('min_score', 8.0);
            $samples = $this->sampleService->getTopScoringSamples($minScore);

            return response()->json([
                'success' => true,
                'data' => $samples,
                'message' => 'Top scoring samples retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve top scoring samples: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sample statistics
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function statistics()
    {
        try {
            $statistics = $this->sampleService->getSampleStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
                'message' => 'Sample statistics retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search samples
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'query' => 'required|string|min:2|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $samples = $this->sampleService->searchSamples($request->query);

            return response()->json([
                'success' => true,
                'data' => $samples,
                'message' => 'Search completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Search failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sellers list for dropdown
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSellers()
    {
        try {
            $sellers = $this->sellerService->getActiveSellersList();

            return response()->json([
                'success' => true,
                'data' => $sellers->map(function ($seller) {
                    return [
                        'id' => $seller->id,
                        'name' => $seller->seller_name,
                        'tea_estate' => $seller->tea_estate_name,
                        'tea_grades' => $seller->tea_grades
                    ];
                }),
                'message' => 'Sellers retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve sellers: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available tea grades
     * 
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTeaGrades()
    {
        try {
            $teaGrades = $this->sampleService->getAvailableTeaGrades();

            return response()->json([
                'success' => true,
                'data' => $teaGrades,
                'message' => 'Tea grades retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve tea grades: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete sample
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->sampleService->deleteSample($id);

            return response()->json([
                'success' => true,
                'message' => 'Sample deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete sample: ' . $e->getMessage()
            ], 500);
        }
    }

  
/**
 * API: Get samples ready for assignment (Mobile App)
 */
public function readyForAssignmentApi(Request $request)
{
    try {
        $samples = $this->buyerAssignmentService->getSamplesReadyForAssignment();
        
        return response()->json([
            'success' => true,
            'data' => [
                'samples' => $samples->map(function ($sample) {
                    return [
                        'id' => $sample->id,
                        'sample_id' => $sample->sample_id,
                        'sample_name' => $sample->sample_name,
                        'batch_id' => $sample->batch_id,
                        'seller' => [
                            'id' => $sample->seller->id,
                            'name' => $sample->seller->seller_name,
                            'tea_estate' => $sample->seller->tea_estate
                        ],
                        'scores' => [
                            'overall' => $sample->overall_score,
                            'aroma' => $sample->aroma_score,
                            'liquor' => $sample->liquor_score,
                            'appearance' => $sample->appearance_score
                        ],
                        'evaluation' => [
                            'evaluated_at' => $sample->evaluated_at?->toISOString(),
                            'evaluated_by' => $sample->evaluatedBy?->name,
                            'comments' => $sample->evaluation_comments
                        ],
                        'status' => $sample->status,
                        'created_at' => $sample->created_at->toISOString()
                    ];
                })->values(),
                'count' => $samples->count()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching samples: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * API: Get assigned samples (Mobile App)
 */
public function assignedSamplesApi(Request $request)
{
    try {
        $samples = $this->buyerAssignmentService->getAssignedSamples();
        
        return response()->json([
            'success' => true,
            'data' => [
                'samples' => $samples->map(function ($sample) {
                    return [
                        'id' => $sample->id,
                        'sample_id' => $sample->sample_id,
                        'sample_name' => $sample->sample_name,
                        'batch_id' => $sample->batch_id,
                        'seller' => [
                            'id' => $sample->seller->id,
                            'name' => $sample->seller->seller_name,
                            'tea_estate' => $sample->seller->tea_estate
                        ],
                        'overall_score' => $sample->overall_score,
                        'assignments' => $sample->buyerAssignments->map(function ($assignment) {
                            return [
                                'id' => $assignment->id,
                                'buyer' => [
                                    'id' => $assignment->buyer->id,
                                    'name' => $assignment->buyer->buyer_name,
                                    'type' => $assignment->buyer->buyer_type,
                                    'email' => $assignment->buyer->email,
                                    'phone' => $assignment->buyer->phone
                                ],
                                'assignment_remarks' => $assignment->assignment_remarks,
                                'dispatch_status' => $assignment->dispatch_status,
                                'dispatch_status_text' => $assignment->dispatch_status_text,
                                'assigned_at' => $assignment->assigned_at->toISOString(),
                                'assigned_by' => $assignment->assignedBy->name,
                                'dispatched_at' => $assignment->dispatched_at?->toISOString(),
                                'tracking_id' => $assignment->tracking_id
                            ];
                        }),
                        'assignments_count' => $sample->buyerAssignments->count(),
                        'status' => $sample->status
                    ];
                })->values(),
                'count' => $samples->count()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching assigned samples: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * API: Get assignments awaiting dispatch (Mobile App)
 */
public function awaitingDispatchApi(Request $request)
{
    try {
        $assignments = $this->buyerAssignmentService->getAssignmentsAwaitingDispatch();
        
        return response()->json([
            'success' => true,
            'data' => [
                'assignments' => $assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'sample' => [
                            'id' => $assignment->sample->id,
                            'sample_id' => $assignment->sample->sample_id,
                            'sample_name' => $assignment->sample->sample_name,
                            'batch_id' => $assignment->sample->batch_id,
                            'sample_weight' => $assignment->sample->sample_weight,
                            'overall_score' => $assignment->sample->overall_score,
                            'seller' => [
                                'id' => $assignment->sample->seller->id,
                                'name' => $assignment->sample->seller->seller_name,
                                'tea_estate' => $assignment->sample->seller->tea_estate
                            ]
                        ],
                        'buyer' => [
                            'id' => $assignment->buyer->id,
                            'name' => $assignment->buyer->buyer_name,
                            'type' => $assignment->buyer->buyer_type,
                            'email' => $assignment->buyer->email,
                            'phone' => $assignment->buyer->phone,
                            'shipping_address' => $assignment->buyer->shipping_address,
                            'shipping_city' => $assignment->buyer->shipping_city,
                            'shipping_state' => $assignment->buyer->shipping_state,
                            'shipping_pincode' => $assignment->buyer->shipping_pincode
                        ],
                        'assignment_remarks' => $assignment->assignment_remarks,
                        'dispatch_status' => $assignment->dispatch_status,
                        'assigned_at' => $assignment->assigned_at->toISOString(),
                        'assigned_by' => $assignment->assignedBy->name,
                        'days_pending' => $assignment->assigned_at->diffInDays(now())
                    ];
                })->values(),
                'count' => $assignments->count(),
                'statistics' => $this->buyerAssignmentService->getAssignmentStatistics()
            ]
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching assignments: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * API: Assign sample to buyers (Mobile App)
 */
public function storeBuyerAssignmentsApi(Request $request, $id)
{
    $request->validate([
        'buyers' => 'required|array|min:1',
        'buyers.*.buyer_id' => 'required|exists:buyers,id',
        'buyers.*.remarks' => 'nullable|string|max:500'
    ]);

    try {
        $sample = $this->buyerAssignmentService->assignSampleToBuyers($id, $request->input('buyers'));

        return response()->json([
            'success' => true,
            'message' => 'Sample successfully assigned to ' . count($request->input('buyers')) . ' buyer(s)',
            'data' => [
                'sample' => [
                    'id' => $sample->id,
                    'sample_id' => $sample->sample_id,
                    'sample_name' => $sample->sample_name,
                    'status' => $sample->status,
                    'assignments_count' => $sample->buyerAssignments->count()
                ]
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error assigning sample: ' . $e->getMessage()
        ], 400);
    }
}

/**
 * API: Get sample assignments (Mobile App)
 */
public function getSampleAssignmentsApi(Request $request, $id)
{
    try {
        $sample = $this->sampleRepository->getSampleWithDetails($id);
        if (!$sample) {
            return response()->json([
                'success' => false,
                'message' => 'Sample not found'
            ], 404);
        }

        $assignments = $this->buyerAssignmentService->getSampleAssignments($id);

        return response()->json([
            'success' => true,
            'data' => [
                'sample' => [
                    'id' => $sample->id,
                    'sample_id' => $sample->sample_id,
                    'sample_name' => $sample->sample_name,
                    'batch_id' => $sample->batch_id,
                    'overall_score' => $sample->overall_score,
                    'seller' => [
                        'name' => $sample->seller->seller_name,
                        'tea_estate' => $sample->seller->tea_estate
                    ]
                ],
                'assignments' => $assignments->map(function ($assignment) {
                    return [
                        'id' => $assignment->id,
                        'buyer' => [
                            'id' => $assignment->buyer->id,
                            'name' => $assignment->buyer->buyer_name,
                            'type' => $assignment->buyer->buyer_type,
                            'email' => $assignment->buyer->email,
                            'phone' => $assignment->buyer->phone
                        ],
                        'assignment_remarks' => $assignment->assignment_remarks,
                        'dispatch_status' => $assignment->dispatch_status,
                        'dispatch_status_text' => $assignment->dispatch_status_text,
                        'assigned_at' => $assignment->assigned_at->toISOString(),
                        'assigned_by' => $assignment->assignedBy->name,
                        'dispatched_at' => $assignment->dispatched_at?->toISOString(),
                        'tracking_id' => $assignment->tracking_id,
                        'can_remove' => $assignment->dispatch_status === 'awaiting_dispatch'
                    ];
                })
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching assignments: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * API: Update dispatch status (Mobile App)
 */
public function updateDispatchStatusApi(Request $request, $assignmentId)
{
    $request->validate([
        'status' => 'required|in:dispatched,delivered,feedback_received',
        'tracking_id' => 'nullable|string|max:100',
        'delivery_notes' => 'nullable|string|max:500'
    ]);

    try {
        $additionalData = [];
        if ($request->filled('tracking_id')) {
            $additionalData['tracking_id'] = $request->input('tracking_id');
        }
        if ($request->filled('delivery_notes')) {
            $additionalData['delivery_notes'] = $request->input('delivery_notes');
        }

        $assignment = $this->buyerAssignmentService->updateDispatchStatus(
            $assignmentId, 
            $request->input('status'),
            $additionalData
        );

        return response()->json([
            'success' => true,
            'message' => 'Dispatch status updated successfully',
            'data' => [
                'assignment' => [
                    'id' => $assignment->id,
                    'dispatch_status' => $assignment->dispatch_status,
                    'dispatch_status_text' => $assignment->dispatch_status_text,
                    'tracking_id' => $assignment->tracking_id,
                    'dispatched_at' => $assignment->dispatched_at?->toISOString()
                ]
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}

/**
 * API: Remove assignment (Mobile App)
 */
public function removeAssignmentApi(Request $request, $assignmentId)
{
    try {
        $this->buyerAssignmentService->removeAssignment($assignmentId);

        return response()->json([
            'success' => true,
            'message' => 'Assignment removed successfully'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => $e->getMessage()
        ], 400);
    }
}

/**
 * API: Get active buyers for assignment (Mobile App)
 */
public function getActiveBuyersApi(Request $request)
{
    try {
        $buyers = $this->buyerRepository->getActiveBuyers();

        return response()->json([
            'success' => true,
            'data' => [
                'buyers' => $buyers->map(function ($buyer) {
                    return [
                        'id' => $buyer->id,
                        'buyer_name' => $buyer->buyer_name,
                        'buyer_type' => $buyer->buyer_type,
                        'buyer_type_text' => $buyer->buyer_type_text,
                        'contact_person' => $buyer->contact_person,
                        'email' => $buyer->email,
                        'phone' => $buyer->phone,
                        'preferred_tea_grades' => $buyer->preferred_tea_grades,
                        'shipping_address' => $buyer->shipping_address,
                        'shipping_city' => $buyer->shipping_city,
                        'shipping_state' => $buyer->shipping_state,
                        'shipping_pincode' => $buyer->shipping_pincode
                    ];
                })->values(),
                'count' => $buyers->count()
            ]
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching buyers: ' . $e->getMessage()
        ], 500);
    }
}

/**
 * API: Get assignment statistics (Mobile App)
 */
public function getAssignmentStatisticsApi(Request $request)
{
    try {
        $statistics = $this->buyerAssignmentService->getAssignmentStatistics();
        
        // Add additional mobile-specific statistics
        $readySamples = $this->buyerAssignmentService->getSamplesReadyForAssignment()->count();
        $assignedSamples = $this->buyerAssignmentService->getAssignedSamples()->count();

        return response()->json([
            'success' => true,
            'data' => array_merge($statistics, [
                'ready_for_assignment' => $readySamples,
                'assigned_samples' => $assignedSamples,
                'last_updated' => now()->toISOString()
            ])
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching statistics: ' . $e->getMessage()
        ], 500);
    }
}
}