<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\ContainerZxffZCW\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/ContainerZxffZCW/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/ContainerZxffZCW.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\ContainerZxffZCW\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \ContainerZxffZCW\App_KernelDevDebugContainer([
    'container.build_hash' => 'ZxffZCW',
    'container.build_id' => '5c20de20',
    'container.build_time' => 1739440491,
    'container.runtime_mode' => \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? 'web=0' : 'web=1',
], __DIR__.\DIRECTORY_SEPARATOR.'ContainerZxffZCW');
