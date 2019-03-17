<?php

function format_response( $internships ) {
    $indexed = [];
    foreach ($internships as $internship) {
        $newInternship = [];
        foreach ($internship as $key => $value) {
            // Remove underscore and attribute id from keys
            $newKey = implode(array_slice(explode('_', $key), 0, -1));
            $newKey = str_replace('.', '', $newKey);
            $newInternship[$newKey] = $value;
        }
        $indexed[] = $newInternship;
    }
    return $indexed;
}
