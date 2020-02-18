{assign var='XWeekDayOfMonth_week_offset' value="`$delayPrefix`XWeekDayOfMonth_week_offset"}
{assign var='XWeekDayOfMonth_day' value="`$delayPrefix`XWeekDayOfMonth_day"}
{assign var='XWeekDayOfMonth_time_hour' value="`$delayPrefix`XWeekDayOfMonth_time_hour"}
{assign var='XWeekDayOfMonth_time_minute' value="`$delayPrefix`XWeekDayOfMonth_time_minute"}

<div class="label"></div>
<div class="content">{$form.$XWeekDayOfMonth_week_offset.html} {$form.$XWeekDayOfMonth_day.html}</div>
<div class="clear"></div>
<div class="label">{ts}After{/ts}</div>
<div class="content">{$form.$XWeekDayOfMonth_time_hour.html} : {$form.$XWeekDayOfMonth_time_minute.html}</div>
<div class="clear"></div>