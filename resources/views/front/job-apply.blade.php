@extends('layouts.front')

@section('header-text')
    <h1 class="hidden-sm-down">{{ ucwords($job->title) }}</h1>
    <h5 class="hidden-sm-down"><i class="icon-map-pin"></i> {{ ucwords($job->location->location) }}</h5>
@endsection

@section('content')


    <form id="createForm" method="POST">
        @csrf
        <input type="hidden" name="job_id" value="{{ $job->id }}">

        <div class="container">
        <div class="row gap-y">

            <div class="col-md-12 fs-12 pt-50 pb-10 bb-1 mb-20">
                <a class="text-dark"
                   href="{{ route('jobs.jobOpenings') }}">@lang('modules.front.jobOpenings')</a> &raquo; <a
                        class="text-dark"
                        href="{{ route('jobs.jobDetail', $job->slug) }}">{{ ucwords($job->title) }}</a> &raquo; <span
                        class="theme-color">@lang('modules.front.applicationForm')</span>
            </div>

                <div class="col-md-4 px-20 pb-30 bb-1">
                    <h5>@lang('modules.front.personalInformation')</h5>
                </div>


                <div class="col-md-8 pb-30 bb-1">

                    <div class="form-group">
                        <input class="form-control form-control-lg" type="text" name="full_name" placeholder="@lang('modules.front.fullName')">
                    </div>

                    <div class="form-group">
                        <input class="form-control form-control-lg" type="email" name="email"
                               placeholder="@lang('modules.front.email')">
                    </div>

                    <div class="form-group">
                        <input class="form-control form-control-lg" type="tel" name="phone"
                               placeholder="@lang('modules.front.phone')">
                    </div>

                    <div class="form-group">
                        <h6>@lang('modules.front.photo')</h6>
                        <input class="select-file" accept=".png,.jpg,.jpeg" type="file" name="photo"><br>
                        <span class="help-block">@lang('modules.front.photoFileType')</span>
                    </div>

                </div>

                <div class="col-md-4 px-20 py-30 bb-1">
                    <h5>@lang('modules.front.resume')</h5>
                </div>


                <div class="col-md-8 py-30 bb-1">

                    <div class="form-group">
                        <input class="select-file" type="file" name="resume"><br>
                    </div>


                </div>
                @if(count($jobQuestion) > 0)
                    <div class="col-md-4 px-20 pb-30 bb-1">
                        <h5>@lang('modules.front.additionalDetails')</h5>
                    </div>

                    <div class="col-md-8 pb-30 bb-1">
                        @forelse($jobQuestion as $question)
                            <div class="form-group">
                                <input class="form-control form-control-lg" type="text" id="answer[{{ $question->question->id}}]" name="answer[{{ $question->question->id}}]" placeholder="{{ $question->question->question }}">
                            </div>
                        @empty
                        @endforelse

                    </div>
                @endif

            <div class="col-md-4 px-20 pt-30 mb-50">
                    <h5>@lang('modules.front.coverLetter')</h5>
                </div>


                <div class="col-md-8 pt-30 mb-50">

                    <div class="form-group">
                        <textarea class="form-control form-control-lg" name="cover_letter" rows="4"></textarea>
                    </div>


                    <button class="btn btn-lg btn-primary btn-block theme-background" id="save-form" type="button">@lang('modules.front.submitApplication')</button>

                </div>

        </div>
    </div>
    </form>
@endsection

@push('footer-script')
    <script>
        $('#save-form').click(function () {
            $.easyAjax({
                url: '{{route('jobs.saveApplication')}}',
                container: '#createForm',
                type: "POST",
                file:true,
                redirect: true,
                // data: $('#createForm').serialize(),
                success: function (response) {
                    if(response.status == 'success'){
                        var successMsg = '<div class="alert alert-success my-100" role="alert">' +
                            response.msg + ' <a class="" href="{{ route('jobs.jobOpenings') }}">@lang("app.view") @lang("modules.front.jobOpenings") <i class="fa fa-arrow-right"></i></a>'
                            '</div>';
                        $('.main-content .container').html(successMsg);
                    }
                },
                error: function (response) {
                   // console.log(response.responseText);
                    handleFails(response);
                }
            })
        });

        function handleFails(response) {
            console.log(response);

            if (typeof response.responseJSON.errors != "undefined") {
                var keys = Object.keys(response.responseJSON.errors);
                console.log(keys);
                $('#createForm').find(".has-error").find(".help-block").remove();
                $('#createForm').find(".has-error").removeClass("has-error");

                    for (var i = 0; i < keys.length; i++) {
                        // Escape dot that comes with error in array fields
                        var key = keys[i].replace(".", '\\.');
                        var formarray = keys[i];

                        // If the response has form array
                        if(formarray.indexOf('.') >0){
                            var array = formarray.split('.');
                            response.responseJSON.errors[keys[i]] = response.responseJSON.errors[keys[i]];
                            key = array[0]+'['+array[1]+']';
                        }

                        var ele = $('#createForm').find("[name='" + key + "']");

                        var grp = ele.closest(".form-group");
                        $(grp).find(".help-block").remove();

                        //check if wysihtml5 editor exist
                        var wys = $(grp).find(".wysihtml5-toolbar").length;

                        if(wys > 0){
                            var helpBlockContainer = $(grp);
                        }
                        else{
                            var helpBlockContainer = $(grp).find("div:first");
                        }
                        if($(ele).is(':radio')){
                            helpBlockContainer = $(grp).find("div:eq(2)");
                        }

                        if (helpBlockContainer.length == 0) {
                            helpBlockContainer = $(grp);
                        }

                        helpBlockContainer.append('<div class="help-block">' + response.responseJSON.errors[keys[i]] + '</div>');
                        $(grp).addClass("has-error");
                    }

                    if (keys.length > 0) {
                        var element = $("[name='" + keys[0] + "']");
                        if (element.length > 0) {
                            $("html, body").animate({scrollTop: element.offset().top - 150}, 200);
                        }
                    }
            }
        }
    </script>
@endpush