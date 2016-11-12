/**
 * Created by Administrator on 9/21/2016.
 */
var canvStage;
var canvLayer;
var initStage;
var editPose;
var nameList = new Array();

$(function () {
    function update(activeAnchor) {
        var group = activeAnchor.getParent();

        var topLeft = group.find('.topLeft')[0];
        var topRight = group.find('.topRight')[0];
        var bottomRight = group.find('.bottomRight')[0];
        var bottomLeft = group.find('.bottomLeft')[0];
        var image = group.find('.image')[0];

        var anchorX = activeAnchor.x();
        var anchorY = activeAnchor.y();

        // update anchor positions
        switch (activeAnchor.name()) {
            case 'topLeft':
                topRight.y(anchorY);
                bottomLeft.x(anchorX);
                break;
            case 'topRight':
                topLeft.y(anchorY);
                bottomRight.x(anchorX);
                break;
            case 'bottomRight':
                bottomLeft.y(anchorY);
                topRight.x(anchorX);
                break;
            case 'bottomLeft':
                bottomRight.y(anchorY);
                topLeft.x(anchorX);
                break;
        }

        image.setPosition(topLeft.getPosition());

        var width = topRight.x() - topLeft.x();
        var height = bottomLeft.y() - topLeft.y();
        if(width && height) {
            image.setSize({width:width, height: height});
        }
    }
    function addAnchor(group, x, y, name) {
        var stage = group.getStage();
        var layer = group.getLayer();

        var anchor = new Kinetic.Circle({
            x: x,
            y: y,
            stroke: '#666',
            fill: '#ddd',
            strokeWidth: 2,
            radius: 8,
            name: name,
            draggable: true,
            dragOnTop: false
        });

        anchor.on('dragmove', function() {
            update(this);
            layer.draw();
        });
        anchor.on('mousedown touchstart', function() {
            group.setDraggable(false);
            this.moveToTop();
        });
        anchor.on('dragend', function() {
            group.setDraggable(true);
            layer.draw();
        });
        // add hover styling
        anchor.on('mouseover', function() {
            var layer = this.getLayer();
            document.body.style.cursor = 'pointer';
            this.setStrokeWidth(4);
            layer.draw();
        });
        anchor.on('mouseout', function() {
            var layer = this.getLayer();
            document.body.style.cursor = 'default';
            this.strokeWidth(2);
            layer.draw();
        });

        group.add(anchor);
    }

    initStage = function () {
        canvStage = new Kinetic.Stage({
            container: 'canvas-container',
            width: 810,
            height: 400
        });

        // create background
        var stageBg = new Kinetic.Rect({
            x: 0,
            y: 0,
            width: canvStage.getWidth(),
            height: canvStage.getHeight(),
            fill: "#dddddd"
        });

        canvLayer = new Kinetic.Layer();
        canvLayer.add(stageBg);
        canvStage.add(canvLayer);
        canvStage.draw();

        $('#pose-id').val(0);
        $('#txt-new-pose').val('');
        $('#txt-new-pose').trigger('input');
        $('#btn-pose-add').val('Add to Guide');
    }

    function addAsset(top, left, width, height, assetSrc, title) {
        var newImg = new Image();
        newImg.onload = function() {
            var asset = new Kinetic.Image({
                x: 50,
                y: 30,
                image: newImg,
                width: width,
                height: height,
                name: assetSrc
            });

            var text = new Kinetic.Text({
                x: 50,
                y: height + 32,
                width: width,
                text: title,
                fontSize: 17,
                fontStyle: 'bold',
                fontFamily: 'Calibri',
                align: 'center',
                fill: 'black'
            });

            var assetGroup = new Kinetic.Group({
                x: top,
                y: left,
                draggable: true
            });

            assetGroup.add(asset);
            assetGroup.add(text);
            canvLayer.add(assetGroup);

            //addAnchor(assetGroup, 0, 0, 'topLeft');
            //addAnchor(assetGroup, width, 0, 'topRight');
            //addAnchor(assetGroup, width, height, 'bottomRight');
            //addAnchor(assetGroup, 0, height, 'bottomLeft');
            assetGroup.on('dragstart', function() {
                this.moveToTop();
            });

            canvLayer.draw();
        };
        newImg.src = assetSrc;
    }

    editPose = function (pose) {
        initStage();

        $('#pose-id').val(pose.id);
        $('#txt-new-pose').val(pose.name);
        $('#txt-new-pose').trigger('input');
        $('#btn-pose-add').val('Update Pose');

        var data = JSON.parse(pose.data);
        for (var i = 0; i < data['children'].length; i++) {
            var node = data['children'][i];
            var type = node['className'];
            if (type == 'Group') {
                var attrs = node['attrs'];
                var imgTop = 0;
                var imgLeft = 0;
                var imgWidth = 0;
                var imgHeight = 0;
                var imgBase = 0;
                var imgSrc = '';
                var imgTitle = '';

                if (attrs != undefined) {
                    imgTop = attrs['x'];
                    imgLeft = attrs['y'];
                }

                for (var j = 0; j < node['children'].length; j++) {
                    var childNode = node['children'][j];
                    var childType = childNode['className'];
                    if (childType == 'Image') {
                        var attrs = childNode['attrs'];
                        imgBase = attrs['y'];
                        imgSrc = attrs['name'];
                    } else if (childType == 'Text') {
                        var attrs = childNode['attrs'];
                        imgWidth = attrs['width'];
                        imgHeight = attrs['y'] - imgBase - 2;
                        imgTitle = attrs['text'];
                    }
                }

                //alert(imgWidth + '-' + imgHeight + '-' + imgSrc + '-' + imgTitle);
                addAsset(imgTop, imgLeft, imgWidth, imgHeight, imgSrc, imgTitle);
            }
        }
    }

    getNameList = function (pose) {
        var data = JSON.parse(pose.data);
        for (var i = 0; i < data['children'].length; i++) {
            var node = data['children'][i];
            var type = node['className'];
            if (type == 'Group') {
                var imgName = '';
                var imgTitle = '';
                for (var j = 0; j < node['children'].length; j++) {
                    var childNode = node['children'][j];
                    var childType = childNode['className'];
                    if (childType == 'Image') {
                        var attrs = childNode['attrs'];
                        imgName = attrs['name'].replace(/^.*[\\\/]/, '');
                    } else if (childType == 'Text') {
                        var attrs = childNode['attrs'];
                        imgTitle = attrs['text'];
                    }
                }

                if (imgTitle != '') {
                    nameList[imgName] = imgTitle;
                }
            }
        }
    }

    initStage();

    var imgDrag;
    $('img.asset-item').on({
        dragstart: function() {
            imgDrag = this;
        }
    });

    $('#canvas-container').on({
        dragover: function(e) {
            e.preventDefault();
        },
        drop: function(e) {
            e.preventDefault();
            if (imgDrag != null) {
                imgObj = imgDrag;
                imgSrc = imgDrag.getAttribute('src');
                imgTitle = imgDrag.getAttribute('title');
                imgName = imgSrc.replace(/^.*[\\\/]/, '');
                bootbox.prompt({
                    title: "Please input name.",
                    value: nameList[imgName],
                    callback: function(result) {
                        if (result != null && result != '') {
                            nameList[imgName] = result;
                            addAsset(0, 0, imgObj.naturalWidth, imgObj.naturalHeight, imgSrc, result);
                        }
                    }
                });
            }
            imgDrag = null;
        }
    });

    $( "img.asset-item" ).dblclick(function() {
        imgObj = this;
        imgSrc = this.getAttribute('src');
        imgTitle = this.getAttribute('title');
        imgName = imgSrc.replace(/^.*[\\\/]/, '');
        bootbox.prompt({
            title: "Please input name.",
            value: nameList[imgName],
            callback: function(result) {
                if (result != null && result != '') {
                    nameList[imgName] = result;
                    addAsset(0, 0, imgObj.naturalWidth, imgObj.naturalHeight, imgSrc, result);
                }
            }
        });
    });

    $(".btn-pose-clear").click(function(){
        initStage();
        $('#txt-new-pose').val('');
    });

    $(window).on("load", function() {
        $(".content-body").css('display', 'block');
        /*$(".content-body").mCustomScrollbar({
            autoHideScrollbar : true,
            theme : "dark-3"
        });*/
        $(".content-body").mThumbnailScroller({
            axis:"y",
            type:"hover-precise"
        });

        $(".home-panel-footer").mThumbnailScroller({
            theme:"hover-classic"
        });

        $(".asset-item-wrap").css('display', 'block');
        /*$(".home-panel-footer").mCustomScrollbar({
            theme : "dark-3",
            axis: "x",
            mouseWheelPixels: 80
        });*/

        $("html").css({'background' : '#726565'});  /* For browsers that do not support gradients */
        //$("html").css({'background' : '-webkit-linear-gradient(left, #69595b , #44404d)'}); /* For Safari 5.1 to 6.0 */
        //$("html").css({'background' : '-o-linear-gradient(right, #69595b, #44404d)'}); /* For Opera 11.1 to 12.0 */
        //$("html").css({'background' : '-moz-linear-gradient(right, #69595b, #44404d)'}); /* For Firefox 3.6 to 15 */
        //$("html").css({'background' : 'linear-gradient(to right, #69595b , #44404d)'}); /* Standard syntax */
        $(".home-title h1").css({'color' : 'white'});
        $(".navbar-nav > li > a ").css({'color' : 'white'});
    });
});