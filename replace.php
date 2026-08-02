<?php
$dir = new RecursiveDirectoryIterator("c:/Users/USER/Desktop/Westhub/resources/views");
$ite = new RecursiveIteratorIterator($dir);
foreach($ite as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $path = $file->getPathname();
        $content = file_get_contents($path);
        $search = "onclick=\"Livewire.dispatch('openModal', { component: 'book-appointment' })\"";
        $replace = "onclick=\"window.dispatchEvent(new CustomEvent('open-appointment'))\"";
        if (strpos($content, $search) !== false) {
            $newContent = str_replace($search, $replace, $content);
            file_put_contents($path, $newContent);
            echo "Updated: $path\n";
        }
    }
}
