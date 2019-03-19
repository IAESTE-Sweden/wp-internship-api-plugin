<?php

function format_response($internships)
{
    $newInternships = [];
    foreach ($internships as $internship) {
        $newInternship = [];
        foreach ($internship as $key => $value) {
            // Remove underscore and attribute id from keys
            $newKey = implode(array_slice(explode('_', $key), 0, -1));
            $newKey = str_replace('.', '', $newKey);
            $newInternship[$newKey] = $value;
        }
        $newInternships[] = $newInternship;
    }
    return $newInternships;
}

function filter_response($internships)
{
    $filtered_data = [];
    foreach ($internships as $index => $internship) {
        foreach ($internship as $key => $value) {
            if ($key == 'Deadline') {
                if (strtotime($value) >= strtotime(date('Y-m-d'))) {
                    $filtered_data[] = $internship;
                }
            }
        }
    }
    return $filtered_data;
}
