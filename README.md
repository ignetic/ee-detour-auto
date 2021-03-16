Detour Pro Auto
===============

For ExpressionEngine
EE2, EE3, EE4, EE5 Compatible
Required Detour Pro addon.

Automatically create redirects for "Detour Pro" addon when renaming an entry or category URL Title. 

Channel Redirects
-----------------
You can set a path for each of the channels which you would like redirected when the entry's URL title is changed. Fields include:

- Channel URI: the path to the entry - an example would be "news/article"
- Wildcard: option to add /%% wildcard to the end of the redirect URL
- Expiry Time: specify when the redirect should expire - example "+1 year"
- Allowed status: to only enable redirects for the selected statuses

Category Redirects
------------------
You can set a path for each of the channel's category which you would like redirected when the category's URL title is changed.

- Category URI: the path to the category - an example would be "news/category"
- Wildcard: option to add /%% wildcard to the end of the redirect URL
- Expiry Time: specify when the redirect should expire - example "+1 year"

General settings:
----------------

- Allowed Time: Only create detours for entries that are older than the specified time. This allows time from when the entry is created in cases where new entries may be changed multiple times before making them live.
	Example: "+1 hour"

