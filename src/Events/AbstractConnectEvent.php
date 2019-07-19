<?php

namespace CloudCreativity\LaravelStripe\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Queue\SerializesModels;

abstract class AbstractConnectEvent
{

    use SerializesModels;

    /**
     * The signed in user at the time of the event.
     *
     * @var Authenticatable|mixed|null
     */
    public $user;

    /**
     * The view that will be rendered.
     *
     * @var string
     */
    public $view;

    /**
     * Additional data to provide to the view.
     *
     * @var array
     */
    public $data;

    /**
     * Get view data.
     *
     * @return array
     */
    abstract protected function defaults();

    /**
     * AbstractConnectEvent constructor.
     *
     * @param Authenticatable|mixed|null $user
     * @param string $view
     * @param array $data
     */
    public function __construct($user, $view, $data = [])
    {
        $this->user = $user;
        $this->view = $view;
        $this->data = $data;
    }

    /**
     * @param array|string $key
     * @param mixed|null $value
     * @return $this
     */
    public function with($key, $value = null)
    {
        if (is_array($key)) {
            $this->data = array_merge($this->data, $key);
        } else {
            $this->data[$key] = $value;
        }

        return $this;
    }

    /**
     * Get all view data.
     *
     * @return array
     */
    public function all()
    {
        return collect($this->data)
            ->merge($this->defaults())
            ->put('user', $this->user)
            ->all();
    }
}
