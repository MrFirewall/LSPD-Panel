<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log; // Log Facade importieren
use Laravel\Socialite\Facades\Socialite;

class LoginController extends Controller
{
    /**
     * Akzeptiert Request, um "remember" zu lesen und speichert es in der Session.
     */
    public function redirectToCfx(Request $request)
    {
        $remember = $request->has('remember');
        session(['login_remember' => $remember]);

        return Socialite::driver('cfx')->redirect();
    }

    /**
     * Verarbeitet den Login ODER den ID-Check, basierend auf der Session.
     */
    public function handleCfxCallback()
    {
        try {
            $cfxUser = Socialite::driver('cfx')->user();

            // --- PRÜFUNG: Wollte der User nur seine ID wissen? ---
            if (session('auth_flow') === 'id_check') {
                session()->forget('auth_flow'); // Session sofort wieder löschen
                
                $cfxId = $cfxUser->getId();
                $cfxName = $cfxUser->getNickname();

                return view('auth.show-id', compact('cfxId', 'cfxName'));
            }

            // --- STANDARD-LOGIN-LOGIK ---
            $user = User::where('cfx_id', $cfxUser->getId())->first();

            if ($user) {
                // ---------------------------------------------------------
                // START: Avatar Fix (Discourse Template Parsing)
                // ---------------------------------------------------------
                
                // 1. Hole die Rohdaten von der API
                $raw = $cfxUser->getRaw();
                
                // 2. Setze den Standard-Fallback (falls kein Template gefunden wird)
                $finalAvatarUrl = $cfxUser->getAvatar(); 

                // 3. Prüfe, ob ein "avatar_template" in den Rohdaten existiert
                if (isset($raw['avatar_template']) && !empty($raw['avatar_template'])) {
                    // Ersetze {size} durch '512' (oder 1024) für hohe Qualität
                    $template = str_replace('{size}', '1024', $raw['avatar_template']);
                    
                    // Prüfen, ob die URL relativ ist (fängt nicht mit http an)
                    if (!str_starts_with($template, 'http')) {
                        // CFX Forum Base URL voranstellen
                        $finalAvatarUrl = 'https://forum.cfx.re' . $template;
                    } else {
                        $finalAvatarUrl = $template;
                    }
                }
                // ---------------------------------------------------------
                // ENDE: Avatar Fix
                // ---------------------------------------------------------

                $user->update([
                    'cfx_name' => $cfxUser->getNickname(),
                    'avatar'   => $finalAvatarUrl, // Hier nutzen wir nun die optimierte URL
                ]);

                // Hole den "remember" Status aus der Session und lösche ihn direkt
                $remember = session()->pull('login_remember', false);

                // Speichere den finalen Status für den JS-Timer (Session Timeout)
                session(['is_remembered' => $remember]);
                
                // User einloggen
                Auth::login($user, $remember); 
                
                // Auth-Flags setzen
                session(['is_cfx_authenticated' => true]);
                
                // Leere Lockscreen-Daten, falls vorhanden
                session()->forget('lockscreen_name');
                session()->forget('lockscreen_avatar');
                session()->forget('is_locked');

                return redirect()->intended(route('dashboard'));
            } else {
                return redirect('/')->with('error', 'Dein Account wurde im System nicht gefunden. Bitte wende dich an die Personalabteilung.');
            }

        } catch (\Exception $e) {
            session()->forget('auth_flow');
            session()->forget('login_remember');
            Log::error('Cfx.re Callback Fehler: ' . $e->getMessage());
            return redirect('/')->with('error', 'Es ist ein Fehler aufgetreten. Bitte erneut versuchen.');
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
    
    // --- METHODEN FÜR DEN ID-CHECK ---

    public function showCheckIdPage()
    {
        return view('auth.check-id');
    }

    /**
     * Setzt die Session und startet den Redirect für den ID-Check.
     */
    public function startCheckIdFlow()
    {
        session(['auth_flow' => 'id_check']);
        return redirect()->route('login.cfx');
    }
}