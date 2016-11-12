@extends('app')

@section('template_title')
	Edit Poses
@endsection

@section('content')
<div class="container pose-page" ng-app="myApp" ng-controller="MainCtrl" ng-init="init('{{Auth::user()->api_token}}', '{{$project->id}}', '{{$project->name}}', '{{$project->date}}');">
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-body pose-content pose-page-body">
					<div class="top-bar">
						<i class="fa fa-print fa-fw btn-print" ng-click="printPoses()" aria-hidden="true"></i>
						<i class="fa fa-trash-o fa-fw btn-delete-all" ng-click="deleteAllPoses()" aria-hidden="true"></i>
					</div>
					</div>
					<div class="pose-edit-panel">
						<div class="sub-title"></div>
						<div class="sub-header">
							<input type="text" id="txt-new-pose" ng-model="poseName" placeholder="Pose Name"/>
							<input type="hidden" id="pose-id" value=""/>
						</div>
						<br /><br />
						<div id="canvas-container"></div>
						<br /><br />
						<div class="sub-bottom">
							<i class="fa fa-floppy-o fa-fw btn-pose-add" id="btn-pose-add" ng-click="insertPose(poseName)" aria-hidden="true"></i>
							<i class="fa fa-trash-o fa-fw btn-pose-clear" ng-click="clearPose()" aria-hidden="true"></i>
						</div>
					</div>
					<div class="pose-body bevelBox">
						<div id="content-body" class="content-body">
							<div ng-repeat="pose in poses" ng-drop="true" ng-drop-success="onSwapComplete($index,$data,$event)">
                                <div class="pose-item" ng-drag="true" ng-drag-data="pose">
									<i class="fa fa-trash-o fa-fw pose-item-delete" ng-click="deletePose([[pose.id]])" aria-hidden="true"></i>
									<i class="fa fa-pencil-square-o fa-fw pose-item-edit" ng-click="editPose([[pose.id]])" aria-hidden="true"></i>
                                    <span>[[pose.name]]</span>
                                    <img src="[[pose.img]]" class="pose-item-img"/>
									<input type="hidden" value="[[pose.data]]" />
                                </div>
							</div>
						</div>
					</div>
					<div class="help-text">
						<span>Select a guest and drag to Post Work Area (upper Left) - It will remember the guests names in future poses!</span>
					</div>
                    <div class="home-panel-footer">
                        <div class="asset-item-list">
                            @foreach($assets as $key => $value)
                                <div class="asset-item-wrap"><img class="asset-item" src="{{ $value->img_src }}" title="{{ $value->name }}"/><br/><span class="asset-item-title">{{ $value->name }}</span></div>
                            @endforeach
                        </div>
                    </div>
				</div>
			</div>
		</div>
	</div>
    <div id="printDiv">
        <div class="printHeader">
            {{ HTML::image("public/assets/logo.png", "Logo", array('class' => 'print-logo-left')) }}
			{{ HTML::image("public/assets/logo.png", "Logo", array('class' => 'print-logo-right')) }}
            <div class="printHeaderData">
                <span>{{$project->name}}</span><br/>
                <span>{{$project->date}}</span>
            </div>
        </div>
        <div id="printContent">
            <table>
                <tbody id="printPoseItem">
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('template_scripts')
	{!! HTML::script('public/js/kinetic-v5.1.0.js', array('type' => 'text/javascript')) !!}
	{!! HTML::script('public/js/angular.js', array('type' => 'text/javascript')) !!}
    {!! HTML::script('public/js/ngDraggable.js', array('type' => 'text/javascript')) !!}
	{!! HTML::script('public/js/KineticApp.js', array('type' => 'text/javascript')) !!}
	{!! HTML::script('public/js/PoseApp.js', array('type' => 'text/javascript')) !!}
    {!! HTML::script('public/js/jquery.mCustomScrollbar.js', array('type' => 'text/javascript')) !!}
    {!! HTML::script('public/js/jquery.mCustomScrollbar.concat.min.js', array('type' => 'text/javascript')) !!}
	{!! HTML::script('public/js/jquery.mThumbnailScroller.js', array('type' => 'text/javascript')) !!}
    {!! HTML::script('public/js/bootbox.js', array('type' => 'text/javascript')) !!}
    {!! HTML::script('public/js/alert.js', array('type' => 'text/javascript')) !!}
    {!! HTML::script('public/js/PrintThis.js', array('type' => 'text/javascript')) !!}
@endsection