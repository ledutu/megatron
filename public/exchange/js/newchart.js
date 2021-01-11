function convertd1(e, t) {
  var n = e.buy / (e.buy + e.sell),
    o = "Strong Buy";
  $("." + t + " .value-sell").html(e.sell),
    $("." + t + " .value-buy").html(e.buy),
    n > 0.5 && (o = strong_sell),
    n < 0.2 ? (o = strong_sell) : n >= 0.2 && n < 0.4 ? (o = sellLange) : n >= 0.4 && n < 0.6 ? (o = neutral) : n >= 0.6 && n < 0.8 ? (o = buyLang) : n >= 0.8 && (o = strong_buy);
  var i = {
    tooltip: { formatter: "{b}{c}" },

    series: [{
      tooltip: { show: !1 },
      name: "",
      type: "gauge",
      radius: "100%",
      z: 1,
      min: 0,
      max: 1,
      center: ["50%", "70%"],
      splitNumber: 5,
      startAngle: 180,
      endAngle: 0,
      axisLine: {
        show: !0,
        lineStyle: {
          width: 5,
          color: [
            [0.2, "#ff0000"],
            [0.4, "#ed6d6d"],
            [0.6, "#fff"],
            [0.8, "#9cffc7"],
            [1, "#00af4b"],
          ],
        },
      },
      axisLabel: { show: !1 },
      axisTick: { show: !1, lineStyle: { color: "auto", width: 0 }, length: -15 },
      splitLine: { length: 5, lineStyle: { width: 2, color: "transparents" } },
      detail: { show: !1 },
      pointer: { show: !1 },
    },
    {
      name: "",
      type: "gauge",
      show: !1,
      radius: "90%",
      min: 0,
      max: 1,
      center: ["50%",window.innerWidth < 1024 ? "72%" :  "75%"],
      label: {
        formatter: function (e) {
          var t = e.toFixed(2);
          return window.innerWidth < 1024 ? "" : 0 == t ? strong_sell : 0.25 == t ? sellLange : 0.5 == t ? neutral : 0.75 == t ? buyLang : 1 == t ? strong_buy : "";
        },
      },
      data: [{ value: n, name: o }],
      splitNumber: 12,
      startAngle: 180,
      endAngle: 0,
      z: 5,
      axisLine: {
        show: !1,
        lineStyle: {
          width: 0,
          color: [
            [0.12, "#ff0000"],
            [0.35, "#ed6d6d"],
            [0.63, "#fff"],
            [0.8, "#9cffc7"],
            [1, "#00af4b"],
          ],
        },
      },
      axisLabel: {
        show: !(window.innerWidth < 992),
        color: "#f4c61c",
        fontSize: 10,
        distance: -77,
        padding: [10, 0, 0, 0],
        formatter: function (e) {
          var t = e.toFixed(2);
          return 0 == t ? '\t\t'+strong_sell : 0.25 == t ? sellLange : 0.5 == t ? neutral : 0.75 == t ? "BUY" : 1 == t ? strong_buy : "";
        },
      },
      axisTick: { splitNumber: 10, show: !1, lineStyle: { color: "auto", width: 2 }, length: 20 },
      splitLine: { show: !1, length: 25, lineStyle: { color: "auto", width: 5 } },
      itemStyle: { normal: { color: "#24D8E7" } },
      pointer: { width: 2, length: "90%" },
      detail: {
        formatter: function (e) {
          return (100 * e).toFixed(0) + "%";
        },
        fontSize: 15,
        color: "#fff",
        offsetCenter: ["0%", "-35%"],
        show: !1,
      },
      title: { offsetCenter: ["0", window.innerWidth < 1024 ? "50%" : "15%"], fontSize: window.innerWidth < 1024 ? 13 : 15, color: "#fff", show: !0 },
    },
    ],
  },
    l = document.getElementById("gua_1"),
    r = echarts.init(l);
  r.setOption(i),
    $(window).on('resize', function () {
      r.resize();
    });

}
function convertd2(e, t) {
  var n = e.buy / (e.buy + e.sell),
    o = strong_buy;
  $(".qua_2 .value-sell").html(e.sell),
    $(".qua_2 .value-buy").html(e.buy),
    n > 0.5 && (o = strong_sell),
    n < 0.2 ? (o = strong_sell) : n >= 0.2 && n < 0.4 ? (o = sellLange) : n >= 0.4 && n < 0.6 ? (o = neutral) : n >= 0.6 && n < 0.8 ? (o = buyLang) : n >= 0.8 && (o = strong_buy);
    var i = {
      tooltip: { formatter: "{b}{c}" },
  
      series: [{
        tooltip: { show: !1 },
        name: "",
        type: "gauge",
        radius: "100%",
        z: 1,
        min: 0,
        max: 1,
        center: ["50%", "70%"],
        splitNumber: 5,
        startAngle: 180,
        endAngle: 0,
        axisLine: {
          show: !0,
          lineStyle: {
            width: 5,
            color: [
              [0.2, "#ff0000"],
              [0.4, "#ed6d6d"],
              [0.6, "#fff"],
              [0.8, "#9cffc7"],
              [1, "#00af4b"],
            ],
          },
        },
        axisLabel: { show: !1 },
        axisTick: { show: !1, lineStyle: { color: "auto", width: 0 }, length: -15 },
        splitLine: { length: 5, lineStyle: { width: 2, color: "transparents" } },
        detail: { show: !1 },
        pointer: { show: !1 },
      },
      {
        name: "",
        type: "gauge",
        show: !1,
        radius: "90%",
        min: 0,
        max: 1,
       center: ["50%",window.innerWidth < 1024 ? "72%" :  "75%"],
        label: {
          formatter: function (e) {
            var t = e.toFixed(2);
            return window.innerWidth < 1024 ? "" : 0 == t ? strong_sell : 0.25 == t ? sellLange : 0.5 == t ? neutral : 0.75 == t ? buyLang : 1 == t ? strong_buy : "";
          },
        },
        data: [{ value: n, name: o }],
        splitNumber: 12,
        startAngle: 180,
        endAngle: 0,
        z: 5,
        axisLine: {
          show: !1,
          lineStyle: {
            width: 0,
            color: [
              [0.12, "#ff0000"],
              [0.35, "#ed6d6d"],
              [0.63, "#fff"],
              [0.8, "#9cffc7"],
              [1, "#00af4b"],
            ],
          },
        },
        axisLabel: {
          show: !(window.innerWidth < 992),
          color: "#f4c61c",
          fontSize: 10,
          distance: -77,
          padding: [10, 0, 10, 0],
          formatter: function (e) {
            var t = e.toFixed(2);
            return 0 == t ? '\t\t'+strong_sell : 0.25 == t ? sellLange : 0.5 == t ? neutral : 0.75 == t ? buyLang : 1 == t ? strong_buy : "";
          },
        },
        axisTick: { splitNumber: 10, show: !1, lineStyle: { color: "auto", width: 2 }, length: 20 },
        splitLine: { show: !1, length: 25, lineStyle: { color: "auto", width: 5 } },
        itemStyle: { normal: { color: "#24D8E7" } },
        pointer: { width: 2, length: "90%" },
        detail: {
          formatter: function (e) {
            return (100 * e).toFixed(0) + "%";
          },
          fontSize: 15,
          color: "#fff",
          offsetCenter: ["0%", "-35%"],
          show: !1,
        },
        title: { offsetCenter: ["0", window.innerWidth < 1024 ? "50%" : "15%"], fontSize: window.innerWidth < 1024 ? 13 : 15, color: "#fff", show: !0 },
      },
      ],
    },
    l = document.getElementById("gua_2"),
    r = echarts.init(l);
  r.setOption(i),
    $(window).on('resize', function () {
      r.resize();
    });
}
function convertd3(e, t) {
  var n = e.buy / (e.buy + e.sell),
    o = strong_buy;
  $(".qua_3 .value-sell").html(e.sell),
    $(".qua_3 .value-buy").html(e.buy),
    n > 0.5 && (o = strong_sell),
    n < 0.2 ? (o = strong_sell) : n >= 0.2 && n < 0.4 ? (o = sellLange) : n >= 0.4 && n < 0.6 ? (o = neutral) : n >= 0.6 && n < 0.8 ? (o = buyLang) : n >= 0.8 && (o = strong_buy);
    var i = {
      tooltip: { formatter: "{b}{c}" },
  
      series: [{
        tooltip: { show: !1 },
        name: "",
        type: "gauge",
        radius: "100%",
        z: 1,
        min: 0,
        max: 1,
        center: ["50%", "70%"],
        splitNumber: 5,
        startAngle: 180,
        endAngle: 0,
        axisLine: {
          show: !0,
          lineStyle: {
            width: 5,
            color: [
              [0.2, "#ff0000"],
              [0.4, "#ed6d6d"],
              [0.6, "#fff"],
              [0.8, "#9cffc7"],
              [1, "#00af4b"],
            ],
          },
        },
        axisLabel: { show: !1 },
        axisTick: { show: !1, lineStyle: { color: "auto", width: 0 }, length: -15 },
        splitLine: { length: 5, lineStyle: { width: 2, color: "transparents" } },
        detail: { show: !1 },
        pointer: { show: !1 },
      },
      {
        name: "",
        type: "gauge",
        show: !1,
        radius: "90%",
        min: 0,
        max: 1,
       center: ["50%",window.innerWidth < 1024 ? "72%" :  "75%"],
        label: {
          formatter: function (e) {
            var t = e.toFixed(2);
            return window.innerWidth < 1024 ? "" : 0 == t ? strong_sell : 0.25 == t ? sellLange : 0.5 == t ? neutral : 0.75 == t ? buyLang : 1 == t ? strong_buy : "";
          },
        },
        data: [{ value: n, name: o }],
        splitNumber: 12,
        startAngle: 180,
        endAngle: 0,
        z: 5,
        axisLine: {
          show: !1,
          lineStyle: {
            width: 0,
            color: [
              [0.12, "#ff0000"],
              [0.35, "#ed6d6d"],
              [0.63, "#fff"],
              [0.8, "#9cffc7"],
              [1, "#00af4b"],
            ],
          },
        },
        axisLabel: {
          show: !(window.innerWidth < 992),
          color: "#f4c61c",
          fontSize: 10,
          distance: -77,
          padding: [10, 0, 10, 0],
          formatter: function (e) {
            var t = e.toFixed(2);
            return 0 == t ? '\t\t'+strong_sell : 0.25 == t ? sellLange : 0.5 == t ? neutral : 0.75 == t ? buyLang : 1 == t ? strong_buy : "";
          },
        },
        axisTick: { splitNumber: 10, show: !1, lineStyle: { color: "auto", width: 2 }, length: 20 },
        splitLine: { show: !1, length: 25, lineStyle: { color: "auto", width: 5 } },
        itemStyle: { normal: { color: "#24D8E7" } },
        pointer: { width: 2, length: "90%" },
        detail: {
          formatter: function (e) {
            return (100 * e).toFixed(0) + "%";
          },
          fontSize: 15,
          color: "#fff",
          offsetCenter: ["0%", "-35%"],
          show: !1,
        },
        title: { offsetCenter: ["0", window.innerWidth < 1024 ? "50%" : "15%"], fontSize: window.innerWidth < 1024 ? 13 : 15, color: "#fff", show: !0 },
      },
      ],
    },
    l = document.getElementById("gua_3"),
    r = echarts.init(l);
  r.setOption(i),
    $(window).on('resize', function () {
      r.resize();
    });
}
