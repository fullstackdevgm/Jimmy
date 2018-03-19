'use strict';

angular.module('myApp',['nvd3'])

    .controller('myCtrl', function($scope){

        $scope.options = {
            chart: {
                type: 'pieChart',
                height: 500,
                x: function(d){return d.key;},
                y: function(d){return d.y;},
                color: function(d){return d.color},
                showLabels: true,
                duration: 500,
                labelThreshold: 0.05,
                labelSunbeamLayout: false,
                donut: true,
                donutLabelsOutside: true,
                donutRatio: 0.35,
                transitionDuration: 500,
                legend: {
                    margin: {
                        top: 5,
                        right: 35,
                        bottom: 5,
                        left: 0
                    }
                },
                tooltip:{
                    enabled: false
                },

            },
            title: {
                    enable: true,
                    text: "Jimmy Data Donut Chart Demo"
            },
            
        };

        $scope.data = [
            {
                key: "Keyword 1 : 100",
                y: 10,
                color: "#1660A1"
            },
            {
                key: "Keyword 2 : 25",
                y: 1,
                color: "#F1B0C4"
            },
            {
                key: "Keyword 3 :24 ",
                y: 3,
                color: "#E14B78"
            },
            {
                key: "Keyword  4: 30 ",
                y: 7,
                color: "#C03A45"
            },
            {
                key: "Keyword  5: 25 ",
                y: 4,
                color: "#E0423F"
            },
            {
                key: "Keyword 6: 24 ",
                y: 3,
                color: "#FD9F2E"
            },
            {
                key: "Keyword 7: 25 ",
                y: 5,
                color: "#F7C00B"
            },
            {
                key: "Keyword 8: 25 ",
                y: 5,
                color: "#D4DE57"
            },
            {
                key: "Keyword 9: 25 ",
                y: 5,
                color: "#47C86B"
            },
            {
                key: "Keyword 10: 25 ",
                y: 5,
                color: "#1F84DC"
            }
        ];
    })