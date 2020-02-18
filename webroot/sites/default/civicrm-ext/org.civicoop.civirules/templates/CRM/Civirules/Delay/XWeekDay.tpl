{assign var='XWeekDay_week_offset' value="`$delayPrefix`XWeekDay_week_offset"}
{assign var='XWeekDay_day' value="`$delayPrefix`XWeekDay_day"}
{assign var='XWeekDay_time_hour' value="`$delayPrefix`XWeekDay_time_hour"}
{assign var='XWeekDay_time_minute' value="`$delayPrefix`XWeekDay_time_minute"}
<div class="label"></div>
<div class="content">{$form.$XWeekDay_week_offset.html} {ts}on{/ts} {$form.$XWeekDay_day.html} </div>
<div class="clear"></div>
<div class="label">{ts}After{/ts}</div>
<div class="content">{$form.$XWeekDay_time_hour.html} : {$form.$XWeekDay_time_minute.html}</div>
<div class="clear"></div>