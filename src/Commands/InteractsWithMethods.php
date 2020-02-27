<?php

namespace BoggyBot\LaravelTestGenerator\Commands;

use Illuminate\Support\Str;

trait InteractsWithMethods
{
    private function applyMethodStubsAndSave($item) : void
    {
        $path = $this->stubs_path."/methods/{$item->method}.stub";

        if (false === file_exists($path)) {
            $this->error('Unable to locate method stub in: '.$path);
            return;
        }

        $stub = file_get_contents($path);

        // Stub out parameters
        $parameters = collect($item->parameters)
            ->map(function($parameter) {
                return '$'.$parameter;
            });

        $route = $item->named_route
            ? "route('{$item->named_route}', [{$parameters->implode(', ')}])"
            : '"'.str_replace($item->parameters, $parameters->toArray(), $item->uri).'"';

        $route_slug = $item->named_route
            ? $item->method.'_'.str_replace(['.', '-'], ['_', '_'], $item->named_route)
            : $item->method.'_'.Str::slug(str_replace('/', '_', $item->uri), '_');

        $stub_merged = str_replace(
            [
                '{{method}}',
                '{{action}}',
                '{{route}}',
                '{{route_slug}}',
                '{{controller_action}}',
            ],
            [
                $item->method,
                $item->action,
                $route,
                $route_slug,
                $item->controller_long.'::'.$item->action.'()',
            ],
            $stub
        );

        $this->saveMethodToTestClass($item, $stub_merged);
    }

    /**
     * @param $item
     * @param $content
     */
    private function saveMethodToTestClass($item, $content) : void
    {
        $path = $item->test_path_file;
        $lines = [];
        $new_lines = [];

        foreach (file($path) as $line) {
            array_push($lines, trim($line));
        }

        foreach (file($path) as $line) {
            array_push($new_lines, $line);

            // then test if the line contains so you dont miss a line
            // because there is a newline of something at the end of it
            if (!in_array('/** @see \\'.$item->controller_long.'::'.$item->action.'() */', $lines)) {
                if (strpos($line, "////") !== false) {
                    array_pop($new_lines);
                    array_push($new_lines, $content);
                    array_push($new_lines, "    ////\r\n");

                    $this->test_count++;
                }
            }
        }

        file_put_contents($path, $new_lines);
    }
}
