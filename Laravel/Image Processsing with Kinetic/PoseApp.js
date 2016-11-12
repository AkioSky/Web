/**
 * Created by Administrator on 9/21/2016.
 */

/* Anguar JS for myApp*/
var myApp = angular.module('myApp', ['ngDraggable']);
myApp.config(function($interpolateProvider) {
    $interpolateProvider.startSymbol('[[');
    $interpolateProvider.endSymbol(']]');
});

myApp.factory('dataFactory', ['$http', function($http) {
    var urlBase = '/api/pose';
    var dataFactory = {};
    dataFactory.getPoses = function (apiToken, projectID) {
        return $http.get(urlBase + '?api_token=' + apiToken + '&project_id=' + projectID);
    };
    dataFactory.getPose = function (id, apiToken, projectID) {
        return $http.get(urlBase + '/' + id + '?api_token=' + apiToken + '&project_id=' + projectID);
    };
    dataFactory.insertPose = function (cust, apiToken, projectID) {
        return $http.post(urlBase + '?api_token=' + apiToken + '&project_id=' + projectID, cust);
    };
    dataFactory.updatePose = function (cust, apiToken, projectID) {
        return $http.put(urlBase + '/' + cust.id + '?api_token=' + apiToken + '&project_id=' + projectID, cust)
    };
    dataFactory.deletePose = function (id, apiToken, projectID) {
        return $http.delete(urlBase + '/' + id + '?api_token=' + apiToken + '&project_id=' + projectID);
    };
    dataFactory.deleteAllPoses = function (apiToken, projectID) {
        return $http.delete(urlBase + '/0?api_token=' + apiToken + '&project_id=' + projectID);
    };
    return dataFactory;
}]);

myApp.controller('MainCtrl', ['$scope', '$window', 'dataFactory', function ($scope, $window, dataFactory) {
    $scope.status;
    $scope.poses;
    $scope.apiToken;
    $scope.projectID;
    $scope.projectName;
    $scope.projectDate;

    $scope.init = function (token, project_id, name, date) {
        $scope.apiToken = token;
        $scope.projectID = project_id;
        $scope.projectName = name;
        $scope.projectDate = date;
        getPoses();
    }

    function getPoses () {
        dataFactory.getPoses($scope.apiToken, $scope.projectID)
            .success(function (custs) {
                $scope.poses = custs;
                for (var i = 0; i < $scope.poses.length; i++) {
                    $window.getNameList($scope.poses[i]);
                }
            })
            .error(function (error) {
                $scope.status = 'Unable to load pose data: ' + error.message;
            });
    }

    $scope.editPose = function (id) {
        var cust = null;
        for (var i = 0; i < $scope.poses.length; i++) {
            var currCust = $scope.poses[i];
            if (currCust.id == id) {
                cust = currCust;
                break;
            }
        }

        if (cust == null) {
            return;
        }

        $window.editPose(cust);
    };

    $scope.updatePose = function (id) {
        var cust;
        for (var i = 0; i < $scope.poses.length; i++) {
            var currCust = $scope.poses[i];
            if (currCust.id === id) {
                cust = currCust;
                break;
            }
        }

        dataFactory.updatePose(cust, $scope.apiToken, $scope.projectID)
            .success(function () {
                $scope.status = 'Updated Pose! Refreshing pose list.';
                $window.getNameList(cust);
            })
            .error(function (error) {
                $scope.status = 'Unable to update pose: ' + error.message;
            });
    };

    $scope.insertPose = function (poseName) {
        //Fake pose data

        if (poseName == undefined || poseName == ''){
            $.alert('Please input Pose Name.');
            return;
        }

        var json = $window.canvLayer.toJSON();
        var cust = {
            id: 0,
            name: poseName,
            img: '',
            data: json
        };

        $window.canvStage.toDataURL({
            callback: function (dataUrl) {
                id = $('#pose-id').val();
                if (id != 0) {
                    var pose = null;
                    for (var i = 0; i < $scope.poses.length; i++) {
                        var currCust = $scope.poses[i];
                        if (currCust.id == id) {
                            pose = currCust;
                            break;
                        }
                    }

                    if (pose != null) {
                        pose.name = cust.name;
                        pose.img = dataUrl;
                        pose.data = cust.data;
                        $scope.updatePose(pose.id);
                        $window.initStage();
                        return;
                    }
                }

                cust.img = dataUrl;
                dataFactory.insertPose(cust, $scope.apiToken, $scope.projectID)
                    .success(function (response) {
                        $scope.status = 'Inserted Pose! Refreshing pose list.';
                        cust.id = response.id;
                        $scope.poses.push(cust);
                        $window.initStage();
                        $window.getNameList(cust);
                    }).
                error(function(error) {
                    $scope.status = 'Unable to insert pose: ' + error.message;
                });
            }
        });
    };

    $scope.deletePose = function (id) {
        $.confirm('Are you sure you want to delete this pose?', function(ret) {
            if (ret) {
                dataFactory.deletePose(id, $scope.apiToken, $scope.projectID)
                    .success(function () {
                        $scope.status = 'Deleted Pose! Refreshing pose list.';
                        for (var i = 0; i < $scope.poses.length; i++) {
                            var cust = $scope.poses[i];
                            if (cust.id == id) {
                                $scope.poses.splice(i, 1);
                                break;
                            }
                        }
                    })
                    .error(function (error) {
                        $scope.status = 'Unable to delete pose: ' + error.message;
                    });
            }
        });
    };

    $scope.deleteAllPoses = function () {
        $.confirm('Are you sure you want to delete all poses?', function(ret) {
            if (ret) {
                dataFactory.deleteAllPoses($scope.apiToken, $scope.projectID)
                    .success(function () {
                        $scope.status = 'Deleted All Poses! Refreshing pose list.';
                        $scope.poses.length = 0;
                    })
                    .error(function (error) {
                        $scope.status = 'Unable to delete all poses: ' + error.message;
                    });
            }
        });
    };

    $scope.onSwapComplete = function (index, obj, evt) {
        var otherObj = $scope.poses[index];
        var tmpName = otherObj.name;
        var tmpImg = otherObj.img;
        var data = otherObj.data;
        $scope.poses[index].name = obj.name;
        $scope.poses[index].img = obj.img;
        $scope.poses[index].data = obj.data;
        obj.name = tmpName;
        obj.img = tmpImg;
        obj.data = data;
        $scope.updatePose(otherObj.id);
        $scope.updatePose(obj.id);
    };

    $scope.printPoses = function () {
        var printTable = document.getElementById("printPoseItem");
        printTable.innerHTML = "";
        for (var i = 0; i < $scope.poses.length; i++) {
            var pose = $scope.poses[i];
            var poseImgData = pose.img;
            var poseNameList = new Array();
            var poseName = pose.name;
            var data = JSON.parse(pose.data);
            for (var j = 0; j < data['children'].length; j++) {
                var node = data['children'][j];
                var type = node['className'];
                if (type == 'Group') {
                    for (var k = 0; k < node['children'].length; k++) {
                        var childNode = node['children'][k];
                        var childType = childNode['className'];
                        if (childType == 'Text') {
                            var attrs = childNode['attrs'];
                            poseNameList.push(attrs['text']);
                        }
                    }
                }
            }

            var poseRow = document.createElement('tr');

            var poseImgTD = document.createElement('td');
            poseImgTD.className += 'printTD1';
            poseImgTD.innerHTML = '<span>' + poseName + '</span>';
            poseImgTD.innerHTML += "<img class='printPoseImg' src='" + poseImgData + "' />";
            poseRow.appendChild(poseImgTD);

            var poseNameTD = document.createElement('td');
            poseNameTD.className += 'printNameTD';
            for (j = 0; j < poseNameList.length; j ++) {
                var poseNameItemSPAN = document.createElement('div');
                poseNameItemSPAN.className += ' printNameDIV';
                poseNameItemSPAN.innerHTML = "<span class='printNameCheckBox'>&#9634</span>" + poseNameList[j];
                poseNameTD.appendChild(poseNameItemSPAN);
            }
            poseRow.appendChild(poseNameTD);

            var poseCheckTD = document.createElement('td');
            poseCheckTD.className += 'printTD3';
            poseCheckTD.innerHTML = "<span class='printNameCheckBox'>&#9634</span>";
            poseRow.appendChild(poseCheckTD);

            printTable.appendChild(poseRow);
        }

        jQuery("#printDiv").printThis();
    };
}]);