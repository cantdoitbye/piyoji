<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SampleService;
use App\Services\SellerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SampleController extends Controller
{
    public function __construct(
        protected SampleService $sampleService,
        protected SellerService $sellerService
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

    /**
     * Get sample details
     * 
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
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
}