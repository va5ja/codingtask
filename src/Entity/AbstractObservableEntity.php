<?php declare(strict_types=1);

namespace App\Entity;

abstract class AbstractObservableEntity implements \SplSubject
{
    public const EVENT_PROPERTY_CHANGE = 'property:change';

    protected $observers = ['*' => []];

    protected function createEventGroup(string $event = '*'): array
    {
        if (!array_key_exists($event, $this->observers)) {
            $this->observers[$event] = [];
        }

        return $this->observers[$event];
    }

    protected function getEventObservers(string $event = '*'): array
    {
        $group = $this->createEventGroup($event);
        $all = $this->observers['*'];

        return array_merge($group, $all);
    }

    public function attach(\SplObserver $observer, string $event = '*'): self
    {
        $this->createEventGroup($event);

        $this->observers[$event][] = $observer;

        return $this;
    }

    public function detach(\SplObserver $observer, string $event = '*'): self
    {
        foreach ($this->getEventObservers($event) as $key => $attachedObserver) {
            if ($observer === $attachedObserver) {
                unset($this->observers[$event][$key]);
            }
        }

        return $this;
    }

    public function notify(string $event = '*', $data = null): self
    {
        foreach ($this->getEventObservers($event) as $observer) {
            $observer->update($this, $event, $data);
        }

        return $this;
    }
}
