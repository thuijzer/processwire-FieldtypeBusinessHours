# Fieldtype Business Hours
ProcessWire Fieldtype for entering business hours (opening hours) 

## Input format
Per day one or multiple (comma separated) ranges can be entered:

`9:00-12:00`

`9:00-12:00, 13:00-17:30` 

Leave a day empty when closed on that day

## Usage in templates

Days are from 1 to 7 where 1 is Monday and 7 is Sunday (ISO-8601)

`echo $page->field_name->isNowOpen() ? 'Now open' : 'Now closed';`

`if($page->field_name[1] == null) { echo 'Closed on Monday'; }`

`if($page->field_name[2]->inRange('11:00')) { echo 'Tuesday at 11:00 open'; }`

`echo $page->field_name[1];`

`echo $page->field_name[1]->entries[0]->getFrom()->format('H:i');`