#HM Events
## Wordpress plugin for simple event management

**HM Events** enables simple management of single date events within Wordpress. 
It creates the post type `events` and adds a metabox with a date/time picker to the edit screen.

### Features
+ Localization: date/time format based on global settings set in `Admin > Settings > General`
+ Pretty permalinks for event archive pages: 
    * Upcoming events: `http://example.com/events/{year*}/{month*}/{day*}/page*/{page*}` 
    * Passed events: `http://example.com/events/passed/page*/{page*}`

\* optional

### Template tags

+ `<?php the_event_date( $date_format ); ?>`
+ `<?php get_event_date( $date_format ); ?>`
