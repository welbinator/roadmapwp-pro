# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.3.3] - 2024-08-27
* Enhancement - Added Summary field and excerpt support
* Fix - Added necessary file to tailwind config file

## [2.3.2] - 2024-06-11
* Enhancement - Updated help page

## [2.3.1] - 2024-06-07
* Bug Fix - Fixed style customizer that was broken if theme had set a background-image
* Bug Fix - Prevent users from creating taxonomy with slug of "type" as this is a reserved term and breaks things
* Feature - Added ability to hide ideas from the Rest API

## [2.3.0] - 2024-04-09
* Bug Fix - Fixed filemtime error
* Feature - Added filter to allow users to add conditional restrictions to shortcodes/blocks
* Feature - Added ability to restrict blocks to students enrolled in specific LearnDash courses

## [2.2.6] - 2024-04-06
* Bug Fix - Filter container background and text not being able to be changed in customizer styles
* Feature - Restrict voting to logged-in users
* Feature - Integration with LearnDash. Restrict voting to students enrolled in specified course/courses.

## [2.2.5] - 2024-04-05
* Hide the filters section if there aren't any filters available
* Namespaced CSS classes to avoid conflicting with other plugins/themes
* Refactored code around enabling comments
* Addressed some things found by wp.org plugin checker

## [2.2.4] - 2024-04-1
* Bug fix - Fixed issue where admins couldn't create ideas from the backend
* Code improvements - changed "status" taxonomy slug to "idea-status" to avoid conflicting with WordPress post status of published/pending/draft
* Enhancement - Plugin now creates Roadmap and Submit an Idea pages with shortcodes upon activation

## [2.2.3.1] - 2024-03-04

### Changed
* Hotfix - Couple changes from previous release didn't actually make it in due to small git-related mistake

## [2.2.3] - 2024-03-04

### Changed
* Feature - Added search bar to Display Ideas block (pro only)
* Bug fix - Fixed tailwind stylesheet enqueue so that it is only included on pages that contain a RoadMapWP block or shortcode
* Bug fix - Display ideas block was showing pending ideas after filtering
* Bug fix - Fixed tailwind defaults that were overriding things
* Enhancement - Removed some code redundancy
* Enhancement - Improved layout of RoadMap Tabs shortcode and block


## [2.2.2] - 2024-03-01

### Changed
* Enhancement - Code improvements. Converted blocks to standard block scaffolding
* Enhancement - Code improvements. Removed old styling code

## [2.2.1] - 2024-02-21

### Changed
* Feature - Moved color settings to customizer, added more style options
* Enhancement - Code improvements

## [2.2.0] - 2024-02-18

### Changed
* Feature - Added ability for admin to choose to only show New Idea Form block to logged-in users
* Feature - Added ability for admin to choose to only show Display Ideas block to logged-in users
* Feature - Added ability for admin to choose to only show Roadmap block to logged-in users
* Feature - Added ability for admin to choose to only show Roadmap Tabs block to logged-in users

## [2.1.0] - 2024-02-01

### Changed
* Feature - Added a class "has-votes" to ideas that have atleast one vote
* Feature - Added ability to change color of Submit Idea button
* Bug - Fixed bug where votes disappeared when filtering ideas
* Bug - Fixed bug that saved string "0" instead of integer 0 when an idea gained a vote and then lost it

## [2.0.5] - 2024-01-27

### Changed
* Updated Help page, added accordion style UI and added taxonomies section
* Fixed issue on front-end where long URLs wouldn't line-break
* Made a few more minor improvements to the code structure

## [2.0.4] - 2024-01-25

### Changed
* Updated namespacing
* Moved licensing code to its own file

## [2.0.3] - 2024-01-24

### Changed
* Code improvements to align free and pro settings in database
* Update release workflow to exclude certain development files from packaged zip

## [2.0.2] - 2024-01-21

### Changed
* Code improvements, added Namespacing, doc blocks etc

## [2.0.1] - 2024-01-16

### New
* Added ability to choose default tab in Roadmap Tabs block

### Changed
* Updated GitHub workflows

## [2.0.0] - 2024-01-15

### New
* Added ability to select taxonomies in roadmap tabs block
* Added ability to select taxonomies in the New Idea Form block
* Added visual indication of actively selected tabs in Roadmap Tabs block and roadmap_tabs shortcode

### Changed
* Fixed issue where correct tags weren't being displayed
* Fixed Roadmap Tabs block so that it only displays published ideas

## [1.4.5] - 2024-01-11

### New
* Added ability to choose status terms in New Idea Form block

### Changed
* Improved layout of ideas (moved read more link to directily inline with excerpt text)

## [1.4.4] - 2024-01-11

### New
* Added single idea block

### Changed
* Updated help page

## [1.4.3] - 2024-01-11

### New
* Enabled comments on/off for single idea shortcode

### Changed
* Updated styles of help page

### Fixed
* Fixed chose template setting

## [1.2.8] - 2023-12-25

### New
* Added option for admins to change status of ideas on the frontend

### Changed
* Updated styling of idea cards and limited excerpt to 20 words

## [1.2.7] - 2023-12-23

### New
* Added attributes to Roadmap shortcode/block

## [1.2.3] - 2023-12-21

### New
* Updated function names in pro so they would not conflict with function names in free, preventing critical error when activating pro

### Fixed
* Fixed blocks not showing due to renaming pro functions

## [1.2.1] - 2023-12-21

### New
* Added free to pro so pro no longer requires free

[1.0.1]: 
[1.0.0]: 