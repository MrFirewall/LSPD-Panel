<?php

namespace App\Http\Controllers;

use App\Models\Rulebook;
use App\Models\ActivityLog; // Dein Log Model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PotentiallyNotifiableActionOccurred; // Dein Event

class RuleController extends Controller
{
    /**
     * Zeigt das Regelwerk an (Index).
     */
    public function index()
    {
        // Sortiert nach 'order_index', damit §1 vor §2 kommt
        $rules = Rulebook::orderBy('order_index', 'asc')->get();
        return view('rules.index', compact('rules'));
    }

    /**
     * Formular zum Erstellen.
     */
    public function create()
    {
        return view('rules.create');
    }

    /**
     * Speichern in DB + Log + Event.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'order_index' => 'integer'
        ]);

        $creator = Auth::user();

        $rule = Rulebook::create([
            'title' => $request->title,
            'content' => $request->content,
            'order_index' => $request->order_index ?? 0,
            'updated_by' => $creator->id
        ]);

        // --- ACTIVITY LOG (Dein Snippet angepasst) ---
        ActivityLog::create([
            'user_id' => $creator->id,
            'log_type' => 'RULEBOOK', // Angepasst für Regelwerk
            'action' => 'CREATED',
            'target_id' => $rule->id,
            'description' => "Regelwerk-Abschnitt '{$rule->title}' wurde erstellt.",
        ]);

        // --- BENACHRICHTIGUNG VIA EVENT ---
        PotentiallyNotifiableActionOccurred::dispatch(
            'RuleController@store', 
            $creator, 
            $rule, 
            $creator 
        );

        return redirect()->route('rules.index')->with('success', 'Abschnitt erfolgreich erstellt.');
    }

    /**
     * Formular zum Bearbeiten.
     */
    public function edit(Rule $rule)
    {
        return view('rules.edit', compact('rule'));
    }

    /**
     * Update + Log + Event.
     */
    public function update(Request $request, Rule $rule)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required',
            'order_index' => 'integer'
        ]);

        $editor = Auth::user();

        $rule->update([
            'title' => $request->title,
            'content' => $request->content,
            'order_index' => $request->order_index,
            'updated_by' => $editor->id
        ]);

        // --- ACTIVITY LOG ---
        ActivityLog::create([
            'user_id' => $editor->id,
            'log_type' => 'RULEBOOK',
            'action' => 'UPDATED',
            'target_id' => $rule->id,
            'description' => "Regelwerk-Abschnitt '{$rule->title}' wurde bearbeitet.",
        ]);

        // --- BENACHRICHTIGUNG ---
        PotentiallyNotifiableActionOccurred::dispatch(
            'RuleController@update', 
            $editor, 
            $rule, 
            $editor 
        );

        return redirect()->route('rules.index')->with('success', 'Abschnitt aktualisiert.');
    }

    /**
     * Löschen.
     */
    public function destroy(Rule $rule)
    {
        $user = Auth::user();
        $title = $rule->title;
        $id = $rule->id;

        $rule->delete();

        // Log für Löschung
        ActivityLog::create([
            'user_id' => $user->id,
            'log_type' => 'RULEBOOK',
            'action' => 'DELETED',
            'target_id' => $id,
            'description' => "Regelwerk-Abschnitt '{$title}' wurde gelöscht.",
        ]);

        // --- BENACHRICHTIGUNG ---
        PotentiallyNotifiableActionOccurred::dispatch(
            'RuleController@destroy', 
            $editor, 
            $rule, 
            $editor 
        );

        return redirect()->route('rules.index')->with('success', 'Abschnitt gelöscht.');
    }
}