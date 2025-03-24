<?php
/**
 * Helper function to get the correct profile picture path
 * This function checks both standard locations for profile pictures
 * and returns the path with a cache-busting timestamp
 */
function getProfileImagePath($profile_pic) {
    // Default image as fallback
    $default_path = '../assets/img/default-avatar.png';
    
    // If it's explicitly the default avatar or empty
    if ($profile_pic === 'default-avatar.png' || empty($profile_pic)) {
        return $default_path;
    }
    
    // Check if custom profile pic exists in uploads directory
    $upload_path = '../uploads/profile/' . $profile_pic;
    if (file_exists($upload_path)) {
        // Return with cache buster
        return $upload_path . '?v=' . time();
    }
    
    // Check if it exists in assets directory
    $asset_path = '../assets/img/' . $profile_pic;
    if (file_exists($asset_path)) {
        return $asset_path . '?v=' . time();
    }
    
    // Fallback to default
    return $default_path;
}