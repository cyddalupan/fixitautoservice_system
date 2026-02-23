<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SkillController extends Controller
{
    /**
     * Display a listing of skills.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Skill::with(['technician']);

        // Filter by technician
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        // Filter by category
        if ($request->has('category')) {
            $query->where('skill_category', $request->category);
        }

        // Filter by proficiency level
        if ($request->has('proficiency_level')) {
            $query->where('proficiency_level', $request->proficiency_level);
        }

        // Filter by primary skills
        if ($request->has('primary_only')) {
            $query->where('is_primary_skill', true);
        }

        // Filter by certification required
        if ($request->has('certification_required')) {
            $query->where('certification_required', true);
        }

        // Filter by expired skills (not used for 2+ years)
        if ($request->has('expired_only')) {
            $twoYearsAgo = Carbon::now()->subYears(2);
            $query->where('last_used_date', '<', $twoYearsAgo)
                  ->orWhereNull('last_used_date');
        }

        $perPage = $request->get('per_page', 20);
        $skills = $query->orderBy('skill_name')->paginate($perPage);

        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $categories = Skill::getSkillCategories();
        $proficiencyLevels = Skill::getProficiencyLevels();

        return view('skills.index', compact('skills', 'technicians', 'categories', 'proficiencyLevels'));
    }

    /**
     * Show the form for creating a new skill.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $categories = Skill::getSkillCategories();
        $proficiencyLevels = Skill::getProficiencyLevels();

        return view('skills.create', compact('technicians', 'categories', 'proficiencyLevels'));
    }

    /**
     * Store a newly created skill.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'skill_name' => 'required|string|max:100',
            'skill_category' => 'nullable|in:' . implode(',', array_keys(Skill::getSkillCategories())),
            'proficiency_level' => 'required|in:' . implode(',', array_keys(Skill::getProficiencyLevels())),
            'years_experience' => 'nullable|numeric|min:0|max:50',
            'last_used_date' => 'nullable|date',
            'is_primary_skill' => 'boolean',
            'certification_required' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate skill for same technician
        $existingSkill = Skill::where('technician_id', $validated['technician_id'])
            ->where('skill_name', $validated['skill_name'])
            ->first();

        if ($existingSkill) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This technician already has this skill. Please update the existing skill instead.');
        }

        $skill = Skill::create($validated);

        return redirect()->route('skills.show', $skill->id)
            ->with('success', 'Skill created successfully.');
    }

    /**
     * Display the specified skill.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $skill = Skill::with(['technician'])->findOrFail($id);
        
        return view('skills.show', compact('skill'));
    }

    /**
     * Show the form for editing the specified skill.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $skill = Skill::findOrFail($id);
        $technicians = User::where('role', 'technician')->where('is_active', true)->get();
        $categories = Skill::getSkillCategories();
        $proficiencyLevels = Skill::getProficiencyLevels();

        return view('skills.edit', compact('skill', 'technicians', 'categories', 'proficiencyLevels'));
    }

    /**
     * Update the specified skill.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $skill = Skill::findOrFail($id);

        $validated = $request->validate([
            'technician_id' => 'required|exists:users,id',
            'skill_name' => 'required|string|max:100',
            'skill_category' => 'nullable|in:' . implode(',', array_keys(Skill::getSkillCategories())),
            'proficiency_level' => 'required|in:' . implode(',', array_keys(Skill::getProficiencyLevels())),
            'years_experience' => 'nullable|numeric|min:0|max:50',
            'last_used_date' => 'nullable|date',
            'is_primary_skill' => 'boolean',
            'certification_required' => 'boolean',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Check for duplicate skill for same technician (excluding current skill)
        $existingSkill = Skill::where('technician_id', $validated['technician_id'])
            ->where('skill_name', $validated['skill_name'])
            ->where('id', '!=', $id)
            ->first();

        if ($existingSkill) {
            return redirect()->back()
                ->withInput()
                ->with('error', 'This technician already has this skill. Please update the existing skill instead.');
        }

        $skill->update($validated);

        return redirect()->route('skills.show', $skill->id)
            ->with('success', 'Skill updated successfully.');
    }

    /**
     * Remove the specified skill.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->delete();

        return redirect()->route('skills.index')
            ->with('success', 'Skill deleted successfully.');
    }

    /**
     * Mark skill as used.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function markAsUsed($id)
    {
        $skill = Skill::findOrFail($id);
        $skill->markAsUsed();

        return redirect()->back()
            ->with('success', 'Skill marked as used. Last used date updated.');
    }

    /**
     * Get technician skills dashboard.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function dashboard(Request $request)
    {
        $technicianId = $request->get('technician_id');
        $category = $request->get('category');

        if ($technicianId) {
            $technician = User::findOrFail($technicianId);
            $skills = Skill::forTechnician($technicianId)->get();
            
            // Calculate skill statistics
            $skillStats = $this->calculateSkillStatistics($skills);

            return view('skills.dashboard-technician', compact('technician', 'skills', 'skillStats'));
        }

        // Get overall skill statistics
        $overallStats = $this->getOverallSkillStatistics();

        // Get top technicians by skill count
        $topTechnicians = User::where('role', 'technician')
            ->where('is_active', true)
            ->withCount(['skills as skill_count' => function($query) {
                $query->where('is_primary_skill', true);
            }])
            ->orderBy('skill_count', 'desc')
            ->limit(10)
            ->get();

        // Get skill distribution by category
        $skillDistribution = Skill::select('skill_category', DB::raw('COUNT(*) as count'))
            ->groupBy('skill_category')
            ->get()
            ->pluck('count', 'skill_category');

        // Get proficiency distribution
        $proficiencyDistribution = Skill::select('proficiency_level', DB::raw('COUNT(*) as count'))
            ->groupBy('proficiency_level')
            ->get()
            ->pluck('count', 'proficiency_level');

        return view('skills.dashboard-overview', compact(
            'overallStats',
            'topTechnicians',
            'skillDistribution',
            'proficiencyDistribution'
        ));
    }

    /**
     * Calculate skill statistics for a technician.
     *
     * @param \Illuminate\Database\Eloquent\Collection $skills
     * @return array
     */
    private function calculateSkillStatistics($skills): array
    {
        $totalSkills = $skills->count();
        $primarySkills = $skills->where('is_primary_skill', true)->count();
        $expiredSkills = $skills->filter(function($skill) {
            return $skill->isExpired();
        })->count();

        // Calculate average proficiency score
        $averageProficiencyScore = $skills->avg(function($skill) {
            return $skill->calculateSkillScore();
        });

        // Calculate total years of experience
        $totalYearsExperience = $skills->sum('years_experience');

        // Get skill categories count
        $categories = $skills->groupBy('skill_category')->map->count();

        return [
            'total_skills' => $totalSkills,
            'primary_skills' => $primarySkills,
            'expired_skills' => $expiredSkills,
            'average_proficiency_score' => round($averageProficiencyScore, 2),
            'total_years_experience' => round($totalYearsExperience, 1),
            'categories' => $categories,
        ];
    }

    /**
     * Get overall skill statistics.
     *
     * @return array
     */
    private function getOverallSkillStatistics(): array
    {
        $stats = Skill::select([
                DB::raw('COUNT(*) as total_skills'),
                DB::raw('COUNT(DISTINCT technician_id) as technicians_with_skills'),
                DB::raw('AVG(years_experience) as average_years_experience'),
                DB::raw('SUM(CASE WHEN is_primary_skill = 1 THEN 1 ELSE 0 END) as primary_skills'),
                DB::raw('SUM(CASE WHEN certification_required = 1 THEN 1 ELSE 0 END) as skills_requiring_certification'),
            ])
            ->first();

        // Calculate expired skills
        $expiredSkills = Skill::where(function($query) {
            $twoYearsAgo = Carbon::now()->subYears(2);
            $query->where('last_used_date', '<', $twoYearsAgo)
                  ->orWhereNull('last_used_date');
        })->count();

        return [
            'total_skills' => $stats->total_skills ?? 0,
            'technicians_with_skills' => $stats->technicians_with_skills ?? 0,
            'average_years_experience' => round($stats->average_years_experience ?? 0, 1),
            'primary_skills' => $stats->primary_skills ?? 0,
            'skills_requiring_certification' => $stats->skills_requiring_certification ?? 0,
            'expired_skills' => $expiredSkills,
        ];
    }

    /**
     * Get skill analytics.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function analytics(Request $request)
    {
        $category = $request->get('category');
        $proficiencyLevel = $request->get('proficiency_level');

        $query = Skill::query();

        if ($category) {
            $query->where('skill_category', $category);
        }

        if ($proficiencyLevel) {
            $query->where('proficiency_level', $proficiencyLevel);
        }

        // Get skill growth over time
        $skillGrowth = Skill::select([
                DB::raw('YEAR(created_at) as year'),
                DB::raw('MONTH(created_at) as month'),
                DB::raw('COUNT(*) as skill_count'),
            ])
            ->groupBy(DB::raw('YEAR(created_at), MONTH(created_at)'))
            ->orderBy('year')
            ->orderBy('month')
            ->get();

        // Get top skills by count
        $topSkills = Skill::select([
                'skill_name',
                DB::raw('COUNT(*) as technician_count'),
                DB::raw('AVG(years_experience) as average_experience'),
            ])
            ->groupBy('skill_name')
            ->orderBy('technician_count', 'desc')
            ->limit(20)
            ->get();

        // Get skill gaps analysis
        $allTechnicians = User::where('role', 'technician')->where('is_active', true)->count();
        $skillGaps = Skill::select([
                'skill_name',
                DB::raw('COUNT(*) as technician_count'),
                DB::raw('ROUND((COUNT(*) / ' . $allTechnicians . ') * 100, 2) as coverage_percentage'),
            ])
            ->groupBy('skill_name')
            ->orderBy('coverage_percentage')
            ->limit(10)
            ->get();

        $categories = Skill::getSkillCategories();
        $proficiencyLevels = Skill::getProficiencyLevels();

        return view('skills.analytics', compact(
            'skillGrowth',
            'topSkills',
            'skillGaps',
            'categories',
            'proficiencyLevels',
            'category',
            'proficiencyLevel'
        ));
    }

    /**
     * Export skills data.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function export(Request $request)
    {
        $query = Skill::with(['technician']);

        // Apply filters
        if ($request->has('technician_id')) {
            $query->where('technician_id', $request->technician_id);
        }

        if ($request->has('category')) {
            $query->where('skill_category', $request->category);
        }

        $skills = $query->orderBy('skill_name')->get();

        $filename = 'technician-skills-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($skills) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Technician',
                'Skill Name',
                'Category',
                'Proficiency Level',
                'Years Experience',
                'Experience Level',
                'Last Used Date',
                'Is Primary Skill',
                'Certification Required',
                'Skill Score',
                'Is Expired',
                'Notes',
                'Created At',
            ]);

            // Data
            foreach ($skills as $skill) {
                fputcsv($file, [
                    $skill->id,
                    $skill->technician->name ?? 'N/A',
                    $skill->skill_name,
                    $skill->skill_category,
                    $skill->proficiency_level,
                    $skill->years_experience,
                    $skill->getExperienceLevel(),
                    $skill->last_used_date,
                    $skill->is_primary_skill ? 'Yes' : 'No',
                    $skill->certification_required ? 'Yes' : 'No',
                    $skill->calculateSkillScore(),
                    $skill->isExpired() ? 'Yes' : 'No',
                    $skill->notes,
                    $skill->created_at,
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get skill recommendations for a technician.
     *
     * @param int $technicianId
     * @return \Illuminate\Http\Response
     */
    public function getRecommendations($technicianId)
    {
        $technician = User::findOrFail($technicianId);
        $currentSkills = Skill::forTechnician($technicianId)->get();

        // Get all available skill categories
        $allCategories = Skill::getSkillCategories();
        
        // Get technician's current categories
        $currentCategories = $currentSkills->pluck('skill_category')->unique()->toArray();

        // Find missing categories
        $missingCategories = array_diff(array_keys($allCategories), $currentCategories);

        $recommendations = [];

        // Recommend skills from missing categories
        foreach ($missingCategories as $category) {
            $recommendations[] = [
                'type' => 'category_gap',
                'category' => $category,
                'category_display' => $allCategories[$category],
                'message' => "Consider adding skills in {$allCategories[$category]} category to broaden expertise.",
                'priority' => 'medium',
            ];
        }

        // Recommend upgrading proficiency for existing skills
        foreach ($currentSkills as $skill) {
            if ($skill->proficiency_level === Skill::PROFICIENCY_BEGINNER && $skill->years_experience >= 1) {
                $recommendations[] = [
                    'type' => 'proficiency_upgrade',
                    'skill_name' => $skill->skill_name,
                    'current_proficiency' => $skill->proficiency_level,
                    'suggested_proficiency' => Skill::PROFICIENCY_INTERMEDIATE,
                    'message' => "Consider upgrading {$skill->skill_name} from Beginner to Intermediate proficiency.",
                    'priority' => 'low',
                ];
            } elseif ($skill->proficiency_level === Skill::PROFICIENCY_INTERMEDIATE && $skill->years_experience >= 3) {
                $recommendations[] = [
                    'type' => 'proficiency_upgrade',
                    'skill_name' => $skill->skill_name,
                    'current_proficiency' => $skill->proficiency_level,
                    'suggested_proficiency' => Skill::PROFICIENCY_ADVANCED,
                    'message' => "Consider upgrading {$skill->skill_name} from Intermediate to Advanced proficiency.",
                    'priority' => 'medium',
                ];
            }
        }

        // Recommend updating expired skills
        $expiredSkills = $currentSkills->filter(function($skill) {
            return $skill->isExpired();
        });

        foreach ($expiredSkills as $skill) {
            $recommendations[] = [
                'type' => 'skill_refresh',
                'skill_name' => $skill->skill_name,
                'last_used' => $skill->last_used_date ? $skill->last_used_date->format('Y-m-d') : 'Never',
                'message' => "Skill '{$skill->skill_name}' hasn't been used for over 2 years. Consider refreshing or removing this skill.",
                'priority' => 'high',
            ];
        }

        // Recommend adding certifications for skills that require them
        $skillsNeedingCertification = $currentSkills->where('certification_required', true);

        foreach ($skillsNeedingCertification as $skill) {
            $recommendations[] = [
                'type' => 'certification_needed',
                'skill_name' => $skill->skill_name,
                'message' => "Skill '{$skill->skill_name}' requires certification. Ensure proper certification is obtained and recorded.",
                'priority' => 'high',
            ];
        }

        // Sort recommendations by priority
        $priorityOrder = ['high' => 3, 'medium' => 2, 'low' => 1];
        usort($recommendations, function($a, $b) use ($priorityOrder) {
            return $priorityOrder[$b['priority']] <=> $priorityOrder[$a['priority']];
        });

        return response()->json([
            'success' => true,
            'technician_id' => $technicianId,
            'technician_name' => $technician->name,
            'total_recommendations' => count($recommendations),
            'recommendations' => $recommendations,
        ]);
    }

    /**
     * Bulk update skills.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'skill_ids' => 'required|array',
            'skill_ids.*' => 'exists:technician_skills,id',
            'action' => 'required|in:mark_as_primary,remove_primary,mark_as_used,delete',
            'notes' => 'nullable|string|max:500',
        ]);

        $skillIds = $validated['skill_ids'];
        $action = $validated['action'];
        $notes = $validated['notes'] ?? '';

        $updatedCount = 0;
        $errors = [];

        foreach ($skillIds as $skillId) {
            try {
                $skill = Skill::findOrFail($skillId);

                switch ($action) {
                    case 'mark_as_primary':
                        $skill->is_primary_skill = true;
                        break;
                    case 'remove_primary':
                        $skill->is_primary_skill = false;
                        break;
                    case 'mark_as_used':
                        $skill->markAsUsed();
                        break;
                    case 'delete':
                        $skill->delete();
                        break;
                }

                if ($action !== 'delete') {
                    if ($notes) {
                        $skill->notes = $notes;
                    }
                    $skill->save();
                }

                $updatedCount++;
            } catch (\Exception $e) {
                $errors[] = "Skill ID {$skillId}: " . $e->getMessage();
            }
        }

        $message = "Successfully {$action} {$updatedCount} skill(s).";
        if (!empty($errors)) {
            $message .= " Errors: " . implode(', ', $errors);
        }

        return redirect()->back()
            ->with('success', $message);
    }
}