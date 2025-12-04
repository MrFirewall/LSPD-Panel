<?php

namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PotentiallyNotifiableActionOccurred
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $controllerAction;
    public $triggeringUser; // Typ-Hint entfernt für Flexibilität (User, Citizen, stdClass)
    public $relatedModel;
    public ?User $actorUser;
    public array $additionalData;

    /**
     * Create a new event instance.
     *
     * @param string $controllerAction
     * @param mixed $triggeringUser  Kann User, Citizen oder object sein
     * @param mixed $relatedModel
     * @param User|null $actorUser
     * @param array $additionalData
     */
    public function __construct(string $controllerAction, $triggeringUser, $relatedModel, ?User $actorUser = null, array $additionalData = [])
    {
        $this->controllerAction = $controllerAction;
        $this->triggeringUser = $triggeringUser;
        $this->relatedModel = $relatedModel;
        $this->actorUser = $actorUser ?? (
            ($triggeringUser instanceof User) ? $triggeringUser : null
        );
        $this->additionalData = $additionalData;
    }
}