<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Rulebook; // WICHTIG: Nutze das Model, das zu deiner Tabelle 'rulebooks' passt
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Events\PotentiallyNotifiableActionOccurred;

class RuleController extends Controller
{
    /**
     * Zeigt das Regelwerk an (Index).
     */
    public function index()
    {
        // Sortiert nach 'order_index'
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

        // 1. Erstellen und in Variable $rule speichern (WICHTIG für den Log unten!)
        $rule = Rulebook::create([
            'title' => $request->title,
            'content' => $request->content,
            'order_index' => $request->order_index ?? 0,
            'updated_by' => $creator->id
        ]);

        // 2. Activity Log (Greift auf $rule zu)
        ActivityLog::create([
            'user_id' => $creator->id,
            'log_type' => 'RULEBOOK',
            'action' => 'CREATED',
            'target_id' => $rule->id, // Hier trat der Fehler auf, jetzt ist $rule definiert
            'description' => "Regelwerk-Abschnitt '{$rule->title}' wurde erstellt.",
        ]);

        // 3. Benachrichtigung
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
    public function edit(Rulebook $rule)
    {
        return view('rules.edit', compact('rule'));
    }

    /**
     * Update + Log + Event.
     */
    public function update(Request $request, Rulebook $rule)
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
    public function destroy(Rulebook $rule)
    {
        $user = Auth::user();
        
        // Daten sichern VOR dem Löschen
        $ruleTitle = $rule->title;
        $ruleId = $rule->id;

        $rule->delete();

        // Log für Löschung
        ActivityLog::create([
            'user_id' => $user->id,
            'log_type' => 'RULEBOOK',
            'action' => 'DELETED',
            'target_id' => $ruleId,
            'description' => "Regelwerk-Abschnitt '{$ruleTitle}' wurde gelöscht.",
        ]);

        // --- BENACHRICHTIGUNG ---
        PotentiallyNotifiableActionOccurred::dispatch(
            'RuleController@destroy', 
            $user, 
            $rule, 
            $user,
            ['title' => $ruleTitle]
        );

        return redirect()->route('rules.index')->with('success', 'Abschnitt gelöscht.');
    }
}