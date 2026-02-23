<?php

namespace App\Http\Controllers;

use App\Models\Certification;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CertificationController extends Controller
{
    /**
     * Display a listing of certifications.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Certification::with(['technician', 'verifier']);

        // Filter by technician
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by status
        if ($request->has('status')) {
            switch ($request->status) {
                case 'active':
                    $query->where('is_active', true)
                          ->where('is_verified', true)
                          ->where(function($q) {
                              $q->whereNull('expiry_date')
                                ->orWhere('expiry_date', '>=', now());
                          });
                    break;
                case 'expired':
                    $query->where('expiry_date', '<', now());
                    break;
                case 'expiring_soon':
                    $thirtyDaysFromNow = now()->addDays(30);
                    $query->whereBetween('expiry_date', [now(), $thirtyDaysFromNow]);
                    break;
                case 'pending_verification':
                    $query->where('is_verified', false);
                    break;
                case 'inactive':
                    $query->where('is_active', false);
                    break;
            }
        }

        // Filter by issuing organization
        if ($request->has('issuing_organization')) {
            $query->where('issuing_organization', $request->issuing_organization);
        }

        // Filter by certification level
        if ($request->has('certification_level')) {
            $query->where('certification_level', $request->certification_level);
        }

        // Filter by verified status
        if ($request->has('is_verified')) {
            $query->where('is_verified', $request->is_verified);
        }

        $perPage = $request->get('per_page', 20);
        $certifications = $query->orderBy('expiry_date', 'desc')->paginate($perPage);

        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $organizations = Certification::getIssuingOrganizations();
        $levels = Certification::getCertificationLevels();

        return view('certifications.index', compact('certifications', 'technicians', 'organizations', 'levels'));
    }

    /**
     * Show the form for creating a new certification.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $organizations = Certification::getIssuingOrganizations();
        $levels = Certification::getCertificationLevels();
        $verifiers = User::whereIn('role', ['admin', 'manager'])->where('is_active', true)->get();

        return view('certifications.create', compact('technicians', 'organizations', 'levels', 'verifiers'));
    }

    /**
     * Store a newly created certification.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'certification_name' => 'required|string|max:255',
            'certification_code' => 'nullable|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'certification_level' => 'nullable|in:' . implode(',', array_keys(Certification::getCertificationLevels())),
            'description' => 'nullable|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'renewal_date' => 'nullable|date|after:issue_date',
            'certification_number' => 'nullable|string|max:255',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_verified' => 'boolean',
            'verified_by' => 'nullable|exists:users,id',
            'verification_notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $filename = 'certificate_' . time() . '_' . $validated['technician_id'] . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('certificates', $filename, 'public');
            $validated['certificate_file_path'] = $path;
        }

        // Set verification date if verified
        if ($validated['is_verified'] ?? false) {
            $validated['verified_date'] = now();
        }

        $certification = Certification::create($validated);

        return redirect()->route('certifications.show', $certification->id)
            ->with('success', 'Certification created successfully.');
    }

    /**
     * Display the specified certification.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $certification = Certification::with(['technician', 'verifier'])->findOrFail($id);
        
        return view('certifications.show', compact('certification'));
    }

    /**
     * Show the form for editing the specified certification.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $certification = Certification::findOrFail($id);
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $organizations = Certification::getIssuingOrganizations();
        $levels = Certification::getCertificationLevels();
        $verifiers = User::whereIn('role', ['admin', 'manager'])->where('is_active', true)->get();

        return view('certifications.edit', compact('certification', 'technicians', 'organizations', 'levels', 'verifiers'));
    }

    /**
     * Update the specified certification.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $certification = Certification::findOrFail($id);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'certification_name' => 'required|string|max:255',
            'certification_code' => 'nullable|string|max:255',
            'issuing_organization' => 'required|string|max:255',
            'certification_level' => 'nullable|in:' . implode(',', array_keys(Certification::getCertificationLevels())),
            'description' => 'nullable|string',
            'issue_date' => 'required|date',
            'expiry_date' => 'nullable|date|after:issue_date',
            'renewal_date' => 'nullable|date|after:issue_date',
            'certification_number' => 'nullable|string|max:255',
            'certificate_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'is_verified' => 'boolean',
            'verified_by' => 'nullable|exists:users,id',
            'verification_notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Handle file upload
        if ($request->hasFile('certificate_file')) {
            // Delete old file if exists
            if ($certification->certificate_file_path && Storage::disk('public')->exists($certification->certificate_file_path)) {
                Storage::disk('public')->delete($certification->certificate_file_path);
            }

            $file = $request->file('certificate_file');
            $filename = 'certificate_' . time() . '_' . $validated['technician_id'] . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('certificates', $filename, 'public');
            $validated['certificate_file_path'] = $path;
        }

        // Update verification date if newly verified
        if (($validated['is_verified'] ?? false) && !$certification->is_verified) {
            $validated['verified_date'] = now();
        }

        $certification->update($validated);

        return redirect()->route('certifications.show', $certification->id)
            ->with('success', 'Certification updated successfully.');
    }

    /**
     * Remove the specified certification.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $certification = Certification::findOrFail($id);

        // Delete certificate file if exists
        if ($certification->certificate_file_path && Storage::disk('public')->exists($certification->certificate_file_path)) {
            Storage::disk('public')->delete($certification->certificate_file_path);
        }

        $certification->delete();

        return redirect()->route('certifications.index')
            ->with('success', 'Certification deleted successfully.');
    }

    /**
     * Download certificate file.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadCertificate($id)
    {
        $certification = Certification::findOrFail($id);

        if (!$certification->certificate_file_path) {
            return redirect()->back()
                ->with('error', 'No certificate file available for download.');
        }

        if (!Storage::disk('public')->exists($certification->certificate_file_path)) {
            return redirect()->back()
                ->with('error', 'Certificate file not found.');
        }

        return Storage::disk('public')->download($certification->certificate_file_path);
    }

    /**
     * Verify certification.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function verify(Request $request, $id)
    {
        $certification = Certification::findOrFail($id);

        $validated = $request->validate([
            'verification_notes' => 'nullable|string|max:1000',
        ]);

        $certification->verify(auth()->id(), $validated['verification_notes'] ?? '');

        return redirect()->back()
            ->with('success', 'Certification verified successfully.');
    }

    /**
     * Unverify certification.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function unverify(Request $request, $id)
    {
        $certification = Certification::findOrFail($id);

        $validated = $request->validate([
            'verification_notes' => 'required|string|max:1000',
        ]);

        $certification->unverify($validated['verification_notes']);

        return redirect()->back()
            ->with('success', 'Certification verification removed.');
    }

    /**
     * Renew certification.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function renew(Request $request, $id)
    {
        $certification = Certification::findOrFail($id);

        $validated = $request->validate([
            'expiry_date' => 'required|date|after:today',
            'renewal_date' => 'nullable|date|after:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $certification->renew(
            $validated['expiry_date'],
            $validated['renewal_date'] ?? null,
            $validated['notes'] ?? ''
        );

        return redirect()->back()
            ->with('success', 'Certification renewed successfully.');
    }

    /**
     * Get certifications dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        $technicianId = $request->get('technician_id');

        if ($technicianId) {
            $technician = User::findOrFail($technicianId);
            $certifications = Certification::forTechnician($technicianId)->get();
            
            // Calculate certification statistics
            $certificationStats = $this->calculateCertificationStatistics($certifications);

            return view('certifications.dashboard-technician', compact('technician', 'certifications', 'certificationStats'));
        }

        // Get overall certification statistics
        $overallStats = $this->getOverallCertificationStatistics();

        // Get expiring soon certifications
        $expiringSoon = Certification::expiringSoon()
            ->with('technician')
            ->orderBy('expiry_date')
            ->limit(10)
            ->get();

        // Get pending verification certifications
        $pendingVerification = Certification::where('is_verified', false)
            ->with('technician')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Get certification distribution by organization
        $organizationDistribution = Certification::select('issuing_organization', DB::raw('COUNT(*) as count'))
            ->groupBy('issuing_organization')
            ->get()
            ->pluck('count', 'issuing_organization');

        // Get certification distribution by level
        $levelDistribution = Certification::select('certification_level', DB::raw('COUNT(*) as count'))
            ->groupBy('certification_level')
            ->get()
            ->pluck('count', 'certification_level');

        return view('certifications.dashboard-overview', compact(
            'overallStats',
            'expiringSoon',
            'pendingVerification',
            'organizationDistribution',
            'levelDistribution'
        ));
    }

    /**
     * Calculate certification statistics for a technician.
     *
     * @param \Illuminate\Database\Eloquent\Collection $certifications
     * @return array
     */
    private function calculateCertificationStatistics($certifications): array
    {
        $totalCertifications = $certifications->count();
        $activeCertifications = $certifications->where('is_active', true)->count();
        $verifiedCertifications = $certifications->where('is_verified', true)->count();
        $expiredCertifications = $certifications->filter(function($cert) {
            return $cert->isExpired();
        })->count();
        $expiringSoonCertifications = $certifications->filter(function($cert) {
            return $cert->isExpiringSoon();
        })->count();

        // Calculate average certification score
        $averageCertificationScore = $certifications->avg(function($cert) {
            return $cert->calculateCertificationScore();
        });

        // Get organizations count
        $organizations = $certifications->groupBy('issuing_organization')->map->count();

        return [
            'total_certifications' => $totalCertifications,
            'active_certifications' => $activeCertifications,
            'verified_certifications' => $verifiedCertifications,
            'expired_certifications' => $expiredCertifications,
            'expiring_soon_certifications' => $expiringSoonCertifications,
            'average_certification_score' => round($averageCertificationScore, 2),
            'organizations' => $organizations,
        ];
    }

    /**
     * Get overall certification statistics.
     *
     * @return array
     */
    private function getOverallCertificationStatistics(): array
    {
        $stats = Certification::select([
                DB::raw('COUNT(*) as total_certifications'),
                DB::raw('COUNT(DISTINCT technician_id) as technicians_with_certifications'),
                DB::raw('SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_certifications'),
                DB::raw('SUM(CASE WHEN is_verified = 1 THEN 1 ELSE 0 END) as verified_certifications'),
                DB::raw('SUM(CASE WHEN expiry_date < NOW() THEN 1 ELSE 0 END) as expired_certifications'),
                DB::raw('SUM(CASE WHEN expiry_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY) THEN 1 ELSE 0 END) as expiring_soon_certifications'),
            ])
            ->first();

        return [
            'total_certifications' => $stats->total_certifications ?? 0,
            'technicians_with_certifications' => $stats->technicians_with_certifications ?? 0,
            'active_certifications' => $stats->active_certifications ?? 0,
            'verified_certifications' => $stats->verified_certifications ?? 0,
            'expired_certifications' => $stats->expired_certifications ?? 0,
            'expiring_soon_certifications' => $stats->expiring_soon_certifications ?? 0,
        ];
    }

    /**
     * Get certification analytics.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function analytics(Request $request)
    {
        $organization = $request->get('organization');
        $level = $request->get('level');

        $query = Certification::query();

        if ($organization) {
            $query->where('issuing_organization', $organization);
        }

        if ($level) {
            $query->where('certification_level', $level);
        }

        // Get certification growth over time
        $certificationGrowth = Certification::select([
                DB::raw('YEAR(issue_date) as year'),
                DB::raw('MONTH(issue_date) as month'),
                DB::raw('COUNT(*) as certification_count'),
            ])
            ->groupBy(DB::raw('YEAR(issue_date), MONTH(issue_date)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Get top certifications by count
        $topCertifications = Certification::select([
                'certification_name',
                'issuing_organization',
                DB::raw('COUNT(*) as technician_count'),
                DB::raw('AVG(TIMESTAMPDIFF(YEAR, issue_date, COALESCE(expiry_date, NOW()))) as average_validity_years'),
            ])
            ->groupBy('certification_name', 'issuing_organization')
            ->orderBy('technician_count', 'desc')
            ->limit(20)
            ->get();

        // Get certification gaps analysis
        $allTechnicians = User::where('role', 'technician')->where('is_active', true)->count();
        $certificationGaps = Certification::select([
                'certification_name',
                DB::raw('COUNT(*) as technician_count'),
                DB::raw('ROUND((COUNT(*) / ' . $allTechnicians . ') * 100, 2) as coverage_percentage'),
            ])
            ->groupBy('certification_name')
            ->orderBy('coverage_percentage')
            ->limit(10)
            ->get();

        $organizations = Certification::getIssuingOrganizations();
        $levels = Certification::getCertificationLevels();

        return view('certifications.analytics', compact(
            'certificationGrowth',
            'topCertifications',
            'certificationGaps',
            'organizations',
            'levels',
            'organization',
            'level'
        ));
    }

    /**
     * Export certifications data.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = Certification::with(['technician', 'verifier']);

        // Apply filters
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->has('organization')) {
            $query->where('issuing_organization', $request->organization);
        }

        $certifications = $query->orderBy('expiry_date', 'desc')->get();

        $filename = 'technician-certifications-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($certifications) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Technician',
                'Certification Name',
                'Certification Code',
                'Issuing Organization',
                'Level',
                'Description',
                'Issue Date',
                'Expiry Date',
                'Renewal Date',
                'Certification Number',
                'Status',
                'Days Until Expiry',
                'Is Verified',
                'Verified By',
                'Verified Date',
                'Is Active',
                'Has Certificate File',
                'Certification Score',
                'Notes',
                'Created At',
            ]);

            // Data
            foreach ($certifications as $cert) {
                fputcsv($file, [
                    $cert->id,
                    $cert->technician->name ?? 'N/A',
                    $cert->certification_name,
                    $cert->certification_code,
                    $cert->issuing_organization,
                    $cert->certification_level,
                    $cert->description,
                    $cert->issue_date ? $cert->issue_date->format('Y-m-d') : null,
                    $cert->expiry_date ? $cert->expiry_date->format('Y-m-d') : null,
                    $cert->renewal_date ? $cert->renewal_date->format('Y-m-d') : null,
                    $cert->certification_number,
                    $cert->getStatus(),
                    $cert->getDaysUntilExpiry(),
                    $cert->is_verified ? 'Yes' : 'No',
                    $cert->verifier->name ?? 'N/A',
                    $cert->verified_date ? $cert->verified_date->format('Y-m-d') : null,
                    $cert->is_active ? 'Yes' : 'No',
                    !empty($cert->certificate_file_path) ? 'Yes' : 'No',
                    $cert->calculateCertificationScore(),
                    $cert->notes,
                    $cert->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get certification alerts (expiring soon, expired, pending verification).
     *
     * @return \Illuminate\Http\Response
     */
    public function getAlerts()
    {
        $expiringSoon = Certification::expiringSoon()
            ->with('technician')
            ->orderBy('expiry_date')
            ->limit(20)
            ->get();

        $expired = Certification::expired()
            ->with('technician')
            ->orderBy('expiry_date', 'desc')
            ->limit(20)
            ->get();

        $pendingVerification = Certification::where('is_verified', false)
            ->with('technician')
            ->orderBy('created_at', 'desc')
            ->limit(20)
            ->get();

        return response()->json([
            'success' => true,
            'alerts' => [
                'expiring_soon' => [
                    'count' => $expiringSoon->count(),
                    'certifications' => $expiringSoon->map(function($cert) {
                        return [
                            'id' => $cert->id,
                            'technician_name' => $cert->technician->name ?? 'N/A',
                            'certification_name' => $cert->certification_name,
                            'expiry_date' => $cert->expiry_date ? $cert->expiry_date->format('Y-m-d') : null,
                            'days_until_expiry' => $cert->getDaysUntilExpiry(),
                        ];
                    }),
                ],
                'expired' => [
                    'count' => $expired->count(),
                    'certifications' => $expired->map(function($cert) {
                        return [
                            'id' => $cert->id,
                            'technician_name' => $cert->technician->name ?? 'N/A',
                            'certification_name' => $cert->certification_name,
                            'expiry_date' => $cert->expiry_date ? $cert->expiry_date->format('Y-m-d') : null,
                            'days_expired' => abs($cert->getDaysUntilExpiry() ?? 0),
                        ];
                    }),
                ],
                'pending_verification' => [
                    'count' => $pendingVerification->count(),
                    'certifications' => $pendingVerification->map(function($cert) {
                        return [
                            'id' => $cert->id,
                            'technician_name' => $cert->technician->name ?? 'N/A',
                            'certification_name' => $cert->certification_name,
                            'created_at' => $cert->created_at->format('Y-m-d'),
                            'days_pending' => $cert->created_at->diffInDays(now()),
                        ];
                    }),
                ],
            ],
        ]);
    }

    /**
     * Bulk update certifications.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'certification_ids' => 'required|array',
            'certification_ids.*' => 'exists:technician_certifications,id',
            'action' => 'required|in:verify,unverify,activate,deactivate,renew,delete',
            'notes' => 'nullable|string|max:500',
            'expiry_date' => 'nullable|date|after:today',
            'renewal_date' => 'nullable|date|after:today',
        ]);

        $certificationIds = $validated['certification_ids'];
        $action = $validated['action'];
        $notes = $validated['notes'] ?? '';
        $expiryDate = $validated['expiry_date'] ?? null;
        $renewalDate = $validated['renewal_date'] ?? null;

        $updatedCount = 0;
        $errors = [];

        foreach ($certificationIds as $certificationId) {
            try {
                $certification = Certification::findOrFail($certificationId);

                switch ($action) {
                    case 'verify':
                        $certification->verify(auth()->id(), $notes);
                        break;
                    case 'unverify':
                        $certification->unverify($notes);
                        break;
                    case 'activate':
                        $certification->activate();
                        break;
                    case 'deactivate':
                        $certification->deactivate($notes);
                        break;
                    case 'renew':
                        if (!$expiryDate) {
                            throw new \Exception('Expiry date required for renewal');
                        }
                        $certification->renew($expiryDate, $renewalDate, $notes);
                        break;
                    case 'delete':
                        // Delete certificate file if exists
                        if ($certification->certificate_file_path && Storage::disk('public')->exists($certification->certificate_file_path)) {
                            Storage::disk('public')->delete($certification->certificate_file_path);
                        }
                        $certification->delete();
                        break;
                }

                if ($action !== 'delete') {
                    if ($notes && $action !== 'verify' && $action !== 'unverify') {
                        $certification->notes = $notes;
                    }
                    $certification->save();
                }

                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Certification ID {$certificationId}: " . $e->getMessage();
            }
        }

        $message = "Successfully {$action} {$updatedCount} certification(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()
            ->with('success', $message);
    }
}