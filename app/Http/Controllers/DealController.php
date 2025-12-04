<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Stage;
use Illuminate\Http\Request;

class DealController extends Controller
{
    public function index()
    {
        $q = request('q');
        $perPage = config('crm.per_column', 6); // per-column page size (from config/crm.php)

        $stages = Stage::orderBy('position')->get();

        // If searching, keep the previous behavior (global paginated results grouped by stage)
        if ($q) {
            $dealsPaged = Deal::with('stage')
                ->where(function ($r) use ($q) {
                    $r->where('title', 'like', '%' . $q . '%')
                        ->orWhere('company', 'like', '%' . $q . '%')
                        ->orWhere('contact_name', 'like', '%' . $q . '%')
                        ->orWhere('contact_email', 'like', '%' . $q . '%');
                })->orderBy('stage_id')->orderBy('position')->paginate(20)->appends(['q' => $q]);

            $grouped = [];
            foreach ($stages as $stage) {
                $grouped[$stage->id] = [];
            }
            foreach ($dealsPaged as $d) {
                $grouped[$d->stage_id][] = $d;
            }

            return view('crm.index', ['stages' => $stages, 'groupedDeals' => $grouped, 'paginator' => $dealsPaged, 'q' => $q]);
        }

        // Paginate for each stage separately. Use unique page query param per stage.
        $paginators = [];
        foreach ($stages as $stage) {
            $pageParam = 'page_stage_' . $stage->id;
            $paginators[$stage->id] = Deal::where('stage_id', $stage->id)
                ->orderBy('position', 'asc')
                ->paginate($perPage, ['*'], $pageParam);
        }

        return view('crm.index', compact('stages', 'paginators'));
    }

    public function create()
    {
        $stages = Stage::orderBy('position')->get();
        return view('crm.create', compact('stages'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
            'stage_id' => 'nullable|exists:stages,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        Deal::create($data);
        return redirect()->route('deals.index')->with('success', 'Oportunidad creada.');
    }

    public function edit(Deal $deal)
    {
        $stages = Stage::orderBy('position')->get();
        return view('crm.create', compact('deal', 'stages'));
    }

    public function update(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'company' => 'nullable|string|max:255',
            'value' => 'nullable|numeric',
            'stage_id' => 'nullable|exists:stages,id',
            'contact_name' => 'nullable|string|max:255',
            'contact_email' => 'nullable|email|max:255',
            'notes' => 'nullable|string',
        ]);

        $deal->update($data);
        return redirect()->route('deals.index')->with('success', 'Oportunidad actualizada.');
    }

    public function destroy(Deal $deal)
    {
        $deal->delete();
        return redirect()->route('deals.index')->with('success', 'Oportunidad eliminada.');
    }

    // Move a deal to another stage (called via AJAX drag & drop)
    public function move(Request $request, Deal $deal)
    {
        $data = $request->validate([
            'stage_id' => 'required|exists:stages,id',
            'position' => 'nullable|integer|min:0',
        ]);

        $newStageId = $data['stage_id'];
        $newPosition = $data['position'] ?? 0;

        // If stage changed, remove from old stage ordering
        if ($deal->stage_id != $newStageId) {
            // decrement positions of deals after this one in old stage
            Deal::where('stage_id', $deal->stage_id)
                ->where('position', '>', $deal->position)
                ->decrement('position');

            // increment positions in new stage from newPosition
            Deal::where('stage_id', $newStageId)
                ->where('position', '>=', $newPosition)
                ->increment('position');

            $deal->stage_id = $newStageId;
            $deal->position = $newPosition;
            $deal->save();
        } else {
            // same stage: reorder within the stage
            $oldPos = $deal->position;
            if ($newPosition == $oldPos) {
                return response()->json(['success' => true]);
            }

            if ($newPosition > $oldPos) {
                // moved down: decrement positions between oldPos+1 and newPosition
                Deal::where('stage_id', $deal->stage_id)
                    ->whereBetween('position', [$oldPos + 1, $newPosition])
                    ->decrement('position');
            } else {
                // moved up: increment positions between newPosition and oldPos-1
                Deal::where('stage_id', $deal->stage_id)
                    ->whereBetween('position', [$newPosition, $oldPos - 1])
                    ->increment('position');
            }

            $deal->position = $newPosition;
            $deal->save();
        }

        return response()->json(['success' => true, 'deal_id' => $deal->id, 'stage_id' => $deal->stage_id, 'position' => $deal->position]);
    }
}
