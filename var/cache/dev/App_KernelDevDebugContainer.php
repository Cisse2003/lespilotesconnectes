<?php

// This file has been auto-generated by the Symfony Dependency Injection Component for internal use.

if (\class_exists(\Container7cSBcCJ\App_KernelDevDebugContainer::class, false)) {
    // no-op
} elseif (!include __DIR__.'/Container7cSBcCJ/App_KernelDevDebugContainer.php') {
    touch(__DIR__.'/Container7cSBcCJ.legacy');

    return;
}

if (!\class_exists(App_KernelDevDebugContainer::class, false)) {
    \class_alias(\Container7cSBcCJ\App_KernelDevDebugContainer::class, App_KernelDevDebugContainer::class, false);
}

return new \Container7cSBcCJ\App_KernelDevDebugContainer([
    'container.build_hash' => '7cSBcCJ',
    'container.build_id' => 'f800537c',
    'container.build_time' => 1739026442,
    'container.runtime_mode' => \in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true) ? 'web=0' : 'web=1',
], __DIR__.\DIRECTORY_SEPARATOR.'Container7cSBcCJ');
