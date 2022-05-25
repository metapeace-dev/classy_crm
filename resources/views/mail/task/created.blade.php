@component('mail::message')
# New Task

@lang('email.newTask.subject')

{{--<h3>@lang('app.task') @lang('app.details')</h3>--}}

@component('mail::text', ['text' => $content])

@endcomponent


@component('mail::button', ['url' => $url])
@lang('app.view') @lang('app.task')
@endcomponent

@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
