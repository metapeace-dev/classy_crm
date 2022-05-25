@component('mail::message')
# Task Details

The Task Details Assigned to You

@component('mail::text', ['text' => $content])

@endcomponent


@component('mail::button', ['url' => $url])
@lang('app.view') @lang('app.task')
@endcomponent

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
