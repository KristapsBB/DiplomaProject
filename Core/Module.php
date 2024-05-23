<?php

namespace DiplomaProject\Core;

use DiplomaProject\Core\Libs\StringHelper;

class Module
{
    private bool $is_configured = false;

    public function configure(array $params)
    {
        if ($this->is_configured) {
            return;
        }

        foreach ($params as $param_name => $param_value) {
            $setter_name = StringHelper::toPascalCase($param_name, '_');
            $this->{'set' . $setter_name}($param_value);
        }

        $this->is_configured = true;
        Core::info(static::class . ' module is configured');
    }

    /**
     * @var callable[][]
     *
     * ```php
     *  [
     *      'event_name' => [
     *          (int) handler_id => [
     *              'handler'    => callable,
     *              'group_name' => 'default',
     *          ],
     *          (int) handler_id => [
     *              'handler'    => callable,
     *              'group_name' => 'system',
     *          ],
     *          (int) handler_id => [
     *              'handler'    => callable,
     *              'group_name' => 'group1',
     *          ],
     *          ...
     *      ]
     *  ]
     * ```
     */
    private array $handlers = [];

    /**
     * adds the event handler for $event_name
     *
     * @return int handler_id
     */
    public function on(string $event_name, callable $handler, string $group_name = 'default'): int
    {
        if (!array_key_exists($event_name, $this->handlers)) {
            $this->handlers[$event_name] = [];
        }

        $this->handlers[$event_name][] = [
            'handler' => $handler,
            'group_name' => $group_name,
        ];

        return array_key_last($this->handlers[$event_name]);
    }

    /**
     * deletes handlers by $event_name AND $group_name,
     * if $group_name is empty, then deletes ALL handlers on $event_name
     */
    public function off(string $event_name, string $group_name = 'default')
    {
        if (!array_key_exists($event_name, $this->handlers)) {
            return;
        }

        if (empty($group_name)) {
            unset($this->handlers[$event_name]);
            return;
        }

        foreach ($this->handlers[$event_name] as $handler_id => $handlers) {
            if ($handlers['group_name'] !== $group_name) {
                continue;
            }

            unset($this->handlers[$event_name][$handler_id]);
        }
    }

    public function offById(string $event_name, int $handler_id)
    {
        if (
            !array_key_exists($event_name, $this->handlers)
            || !array_key_exists($handler_id, $this->handlers[$event_name])
        ) {
            return;
        }

        unset($this->handlers[$event_name][$handler_id]);
    }

    /**
     * triggers event handlers in order of addition,
     * if the group name is empty, then handlers from all groups are executed
     *
     * @param array $arguments will be passed to each handler
     */
    public function trigger(string $event_name, string $group_name = '', array ...$arguments)
    {
        if (!array_key_exists($event_name, $this->handlers)) {
            return;
        }

        foreach ($this->handlers[$event_name] as $handler_date) {
            if (empty($group_name) || $handler_date['group_name'] === $group_name) {
                $handler_date['handler'](...$arguments);
            }
        }
    }
}
