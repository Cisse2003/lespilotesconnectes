<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerY7KELoq\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerY7KELoq/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerY7KELoq.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerY7KELoq\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \ContainerY7KELoq\App_KernelDevDebugContainer([
    'container.build_hash' => 'Y7KELoq',
    'container.build_id' => 'fc2b5b6e',
    'container.build_time' => 1740647803,
    'container.runtime_mode' => \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? 'web=0' : 'web=1',
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerY7KELoq');
