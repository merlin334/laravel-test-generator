<?php

namespace BoggyBot\LaravelTestGenerator\Commands;

trait InteractsWithClass
{

    /**
     * @param $item
     * @return false|string
     */
    public function applyClassStubAndSave($item)
    {
        $stub = file_get_contents($this->stubs_path.'ClassTestStub.stub');

        $stub_merged = str_replace(
            [
                '{{namespace}}',
                '{{controller}}',
                '{{signature}}',
            ],
            [
                $item->test_namespace,
                $item->test_class,
                $item->test_namespace.'\\'.$item->controller,
            ],
            $stub
        );

        $this->saveTestClassFile($item, $stub_merged);
    }

    private function saveTestClassFile($item, $content)
    {
        if (!is_dir($item->test_path)) {
            mkdir($item->test_path, 0777, true);
        }

        if (false === file_exists($item->test_path_file)) {
            file_put_contents($item->test_path_file, $content);
        }
    }

}
