<?php
function filterToursByDestination($tours, $destination) {
    return array_filter($tours, function($tour) use ($destination) {
        return $tour['destination'] === $destination;
    });
}

function filterToursByCategory($tours, $category) {
    return array_filter($tours, function($tour) use ($category) {
        return $tour['category'] === $category;
    });
}

function getFilteredTours($tours, $destination = null, $category = null) {
    if ($destination) {
        $tours = filterToursByDestination($tours, $destination);
    }
    if ($category) {
        $tours = filterToursByCategory($tours, $category);
    }
    return $tours;
}
?>