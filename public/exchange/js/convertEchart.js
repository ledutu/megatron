function showChart(e, t) {
    e = (function (e) {
        for (var t = [], a = [], o = [], i = 0; i < e.length; i++) t.push(e[i]), a.push(e[i].splice(0, 1)[0]), o.push(e[i][4]);
        return { datas: t, times: a, vols: o };
    })(e);
    var a = window.innerWidth <= 768 ? 75 : 20,
        o = 0,
        i = 0,
        n = 0,
        l = 0,
        s = 0,
        r = 0,
        d = "#6ABD45",
        h = "#BE1E2D";
    function m(e, t) {
        return t >= 60 ? parseInt(e.slice(0, 2)) + 1 + ":00" : t < 10 ? e.slice(0, 2) + ":0" + t : e.slice(0, 2) + ":" + t;
    }
    document.getElementById("Poskdata"), document.getElementById("Posmadata"), document.getElementById("Posvoldata"), document.getElementById("Setoption");
    function c() {
        var e = [];
        return 0 !== o && e.push("MA" + o[1]), 0 !== i && e.push("MA" + i[1]), 0 !== n && e.push("MA" + n[1]), 0 !== l && e.push("MA" + l[1]), 0 !== s && e.push("MA" + s[1]), 0 !== r && e.push("MA" + r[1]), e;
    }
    function p(e, t, a) {
        var o, i, n, l, s;
        if (((o = []), a))
            for (i = 0, n = t.length; i < n; i++)
                if (i < e - 1) o.push(NaN);
                else {
                    for (s = 0, l = 0; l < e; l++) s += t[i - l][a];
                    o.push(s / e);
                }
        else
            for (i = 0, n = t.length; i < n; i++)
                if (i < e) o.push(NaN);
                else {
                    for (s = 0, l = 0; l < e; l++) s += t[i - l];
                    o.push(s / e);
                }
        return [o, e];
    }
    (o = p(3, e.datas, 1)), (i = p(5, e.datas, 1));
    var g = [-40, 0],
        x = {
            animation: !0,
            responsive: !0,
            maintainAspectRatio: !1,
            tooltip: { show: !0, trigger: "axis", triggerOn: "mousemove|click", axisPointer: { type: "cross" } },
            xAxis: [
                { show: !0, scale: !0, nameGap: 15, gridIndex: 0, splitNumber: 5, axisLine: { lineStyle: { color: "#4a657a" } }, axisLabel: { show: !1 }, axisTick: { show: !1 }, data: e.times, axisPointer: { label: { show: !1 } } },
                { show: !0, scale: !0, nameGap: 15, gridIndex: 1, splitNumber: 5, axisLabel: { show: !1 }, axisTick: { show: !1 }, data: e.times, axisPointer: { label: { show: !1 } } },
                { show: !0, scale: !0, gridIndex: 2, splitNumber: 5, axisLine: { lineStyle: { color: "#4a657a" } }, axisLabel: { textStyle: { color: "#fff" } }, data: e.times },
                { gridIndex: 3, show: !1, type: "value" },
            ],
            yAxis: [
                {
                    position: "right",
                    scale: !0,
                    gridIndex: 0,
                    axisLine: { show: !1, lineStyle: { color: "#ccc" } },
                    axisLabel: { show: !0, textStyle: { color: "#fff" } },
                    splitLine: { show: !0, lineStyle: { color: "rgb(0 0 0 / 0.1)", width: 1 } },
                },
                { position: "right", gridIndex: 1, splitNumber: 2, minInterval: 0, axisLine: { lineStyle: { color: "#4a657a" } }, axisLabel: { textStyle: { color: "#fff" } }, splitLine: { show: !1, lineStyle: { color: "4a657a" } } },
                { position: "right", gridIndex: 2, splitNumber: 3, show: !1, axisLine: { lineStyle: { color: "#fff" } }, axisLabel: { show: !1, textStyle: { color: "#fff" } }, splitLine: { show: !1, lineStyle: { color: "4a657a" } } },
                {
                    gridIndex: 3,
                    show: !1,
                    type: "category",
                    axisLabel: {
                        showMinLabel: !1,
                        formatter: function (e) {
                            return "￥" + e;
                        },
                        textStyle: { color: "#fff" },
                    },
                    splitLine: { show: !1, lineStyle: { color: "#fff" } },
                    axisLine: { show: !1, lineStyle: { color: "transparent" } },
                },
            ],
            title: { text: "BTC/USDT", color: "#fff", show: !0, textStyle: { color: "#fff" } },
            dataZoom: [
                { show: !1, type: "", start: a, end: 100, xAxisIndex: [0, 0] },
                { show: !1, type: "slider", start: a, end: 100, xAxisIndex: [0, 1] },
                { show: !1, type: "slider", start: a, end: 100, xAxisIndex: [0, 2] },
                { show: !1 },
            ],
            axisPointer: { show: !0, type: "line", link: [{ xAxisIndex: "all" }] },
            toolbox: { Show: !1 },
            series: [
                {
                    right: "30%",
                    padding: 5,
                    
                    type: "candlestick",
                    name: "BTCUSDT",
                    xAxisIndex: 0,
                    yAxisIndex: 0,
                    data: e.datas,
                    markPoint: {
                        symbol: "circle",
                        symbolSize: function (e, t) {
                            let a = 13;
                            return ("Highest price" !== t.name && "Lowest price" !== t.name) || (a = 0.1), a;
                        },
                        label: {
                            z: -1,
                            show: !0,
                            fontSize: 12,
                            color: "#fff",
                            formatter: function (e) {
                                let t = "";
                                return "punctuation" === e.name ? (t = e.value) : "Lowest price" === e.name ? (t = e.value + " →") : "Highest price" === e.name && (t = e.value + " →"), t;
                            },
                        },
                        data: [
                            { name: "Highest price", type: "max", valueDim: "highest", symbolOffset: g, itemStyle: { color: h } },
                            { name: "Lowest price", type: "min", valueDim: "lowest", symbolOffset: g, itemStyle: { color: "rgb(41,60,85)" } },
                        ],
                    },
                    markLine: {
                        z: 5,
                        symbol: "",
                        data: [
                            {
                                yAxis: e.datas[e.datas.length - 1][1],
                                zlevel: 1e4,
                                label: {
                                    formatter: function (a) {
                                        return e.datas[e.datas.length - 1][1].toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,") + "\n" + m(e.times[e.times.length - 1], t);
                                    },
                                    zlevel: 1e4,
                                    show: !0,
                                    color: e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] == 0 ? "black" : "#fff",
                                    position: window.innerWidth <= 768 ? "insideMiddleBottom" : "center",
                                    "z-index": 1050,
                                    padding: 2,
                                    borderRadius: 5,
                                    backgroundColor: e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] == 0 ? "orange" : e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] > 0 ? d : h,
                                },
                                lineStyle: { color: e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] == 0 ? "orange" : e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] > 0 ? d : h, width: 1, type: "dashed" },
                            },
                        ],
                    },
                    itemStyle: { color: d, color0: h, borderColor: d, borderColor0: h },
                },
                { type: "line", name: c()[0], data: o[0], smooth: !0, showSymbol: !1, lineStyle: { width: 1.5, color: "red" } },
                { type: "line", name: c()[1], data: i[0], smooth: !0, showSymbol: !1, lineStyle: { width: 1.5, color: "blue" } },
                { type: "line", name: c()[2], data: n[0], smooth: !0, showSymbol: !1, lineStyle: { width: 1.5, color: "rgb(0 118 255)" } },
                { type: "line", name: c()[3], data: l[0], smooth: !0, showSymbol: !1, lineStyle: { width: 1 } },
                { type: "line", name: c()[4], data: s[0], smooth: !0, showSymbol: !1, lineStyle: { width: 1 } },
                { type: "line", name: c()[5], data: r[0], smooth: !0, showSymbol: !1, lineStyle: { width: 1 } },
                {
                    type: "bar",
                    name: "Volume",
                    xAxisIndex: 1,
                    yAxisIndex: 1,
                    data: e.vols,
                    barCategoryGap: "40%",
                    itemStyle: {
                        normal: {
                            color: function (t) {
                                return e.datas[t.dataIndex][1] > e.datas[t.dataIndex][0] ? d : h;
                            },
                        },
                    },
                    markLine: {
						z:5,
                        symbol: "",
                        data: [
                            {
                                yAxis: e.vols[e.vols.length - 1],
                                label: {
                                    formatter: function (a) {
                                        return e.vols[e.vols.length - 1].toFixed(2).replace(/\d(?=(\d{3})+\.)/g, "$&,") + "\n" + m(e.times[e.times.length - 1], t);
                                    },
                                    show: !0,
                                    color: e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] == 0 ? "black" : "#fff",
                                    position: "center",
                                    padding: 3,
                                    borderRadius: 5,
                                    backgroundColor: e.datas[e.vols.length - 1] - e.datas[e.vols.length - 2] == 0 ? "orange" : e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] > 0 ? d : h,
                                },
                                lineStyle: { color: e.datas[e.vols.length - 1] - e.datas[e.vols.length - 2] == 0 ? "orange" : e.datas[e.datas.length - 1][1] - e.datas[e.datas.length - 2][1] > 0 ? d : h, width: 1, type: "dashed" },
                            },
                        ],
                    },
                },
                { type: "line", xAxisIndex: 3, yAxisIndex: 3, areaStyle: { color: "red", opacity: 0.2 } },
                { type: "line", xAxisIndex: 3, yAxisIndex: 3, areaStyle: { color: "red", opacity: 0.2 } },
            ],
            legend: [
                { textStyle: { color: "#fff" }, data: [c()[0], c()[1], c()[2]], color: "red", show: !0, padding: 5, itemGap: 10, itemWidth: 20, itemHeight: 14, top: "0%", right: window.innerWidth <= 475 ? "0px" : "10%", margin: "auto" },
                { show: !1, padding: 5, itemGap: 10, itemWidth: 25, itemHeight: 14 },
            ],
            grid: [
                { show: !1, top: "10%", left: "0.5%", right: "5%", width: window.innerWidth <= 800 ? (window.innerWidth <= 480 ? (window.innerWidth <= 380 ? "80%" : "87%") : "91%") : "", bottom: "25%", borderColor: "red" },
                {
                    show: !1,
                    left: "0.5%",
                    right: "5%",
                    top: "83%",
                    width: window.innerWidth <= 800 ? (window.innerWidth <= 480 ? "80%" : "91%") : "",
                    bottom: window.innerWidth <= 768 ? "30px" : window.innerHeight <= 500 ? "10%" : "30px",
                    borderColor: "blue",
                },
                { show: !1, left: "0.5%", top: "75%", right: "5%", bottom: "30px", borderColor: "green" },
                { left: "92%", right: "0%", borderColor: "transparent" },
            ],
        },
        w = document.getElementById("MainCharts"),
        y = echarts.init(w);
    y.setOption(x),
        (window.onresize = function () {
            y.resize();
        });
}
