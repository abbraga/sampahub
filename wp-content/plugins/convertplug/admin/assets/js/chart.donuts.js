(function() {
    "use strict";
    var t = this, e = t.Chart, i = e.helpers, s = {
        segmentShowStroke: !0,
        segmentStrokeColor: "#fff",
        segmentStrokeWidth: 2,
        percentageInnerCutout: 50,
        animationSteps: 100,
        animationEasing: "easeOutBounce",
        animateRotate: !0,
        animateScale: !1,
        legendTemplate: '<ul class="<%=name.toLowerCase()%>-legend"><% for (var i=0; i<segments.length; i++){%><li><span style="background-color:<%=segments[i].fillColor%>"><%if(segments[i].label){%><%=segments[i].label%><%}%></span></li><%}%></ul>'
    };
    e.Type.extend({
        name: "Doughnut",
        defaults: s,
        initialize: function(t) {
            this.segments = [], this.outerRadius = (i.min([ this.chart.width, this.chart.height ]) - this.options.segmentStrokeWidth / 2) / 2, 
            this.SegmentArc = e.Arc.extend({
                ctx: this.chart.ctx,
                x: this.chart.width / 2,
                y: this.chart.height / 2
            }), this.options.showTooltips && i.bindEvents(this, this.options.tooltipEvents, function(t) {
                var e = "mouseout" !== t.type ? this.getSegmentsAtEvent(t) : [];
                i.each(this.segments, function(t) {
                    t.restore([ "fillColor" ]);
                }), i.each(e, function(t) {
                    t.fillColor = t.highlightColor;
                }), this.showTooltip(e);
            }), this.calculateTotal(t), i.each(t, function(e, i) {
                e.color || (e.color = "hsl(" + 360 * i / t.length + ", 100%, 50%)"), this.addData(e, i, !0);
            }, this), this.render();
        },
        getSegmentsAtEvent: function(t) {
            var e = [], s = i.getRelativePosition(t);
            return i.each(this.segments, function(t) {
                t.inRange(s.x, s.y) && e.push(t);
            }, this), e;
        },
        addData: function(t, e, i) {
            var s = e || this.segments.length;
            this.segments.splice(s, 0, new this.SegmentArc({
                value: t.value,
                outerRadius: this.options.animateScale ? 0 : this.outerRadius,
                innerRadius: this.options.animateScale ? 0 : this.outerRadius / 100 * this.options.percentageInnerCutout,
                fillColor: t.color,
                highlightColor: t.highlight || t.color,
                showStroke: this.options.segmentShowStroke,
                strokeWidth: this.options.segmentStrokeWidth,
                strokeColor: this.options.segmentStrokeColor,
                startAngle: 1.5 * Math.PI,
                circumference: this.options.animateRotate ? 0 : this.calculateCircumference(t.value),
                label: t.label
            })), i || (this.reflow(), this.update());
        },
        calculateCircumference: function(t) {
            return this.total > 0 ? 2 * Math.PI * (t / this.total) : 0;
        },
        calculateTotal: function(t) {
            this.total = 0, i.each(t, function(t) {
                this.total += Math.abs(t.value);
            }, this);
        },
        update: function() {
            this.calculateTotal(this.segments), i.each(this.activeElements, function(t) {
                t.restore([ "fillColor" ]);
            }), i.each(this.segments, function(t) {
                t.save();
            }), this.render();
        },
        removeData: function(t) {
            var e = i.isNumber(t) ? t : this.segments.length - 1;
            this.segments.splice(e, 1), this.reflow(), this.update();
        },
        reflow: function() {
            i.extend(this.SegmentArc.prototype, {
                x: this.chart.width / 2,
                y: this.chart.height / 2
            }), this.outerRadius = (i.min([ this.chart.width, this.chart.height ]) - this.options.segmentStrokeWidth / 2) / 2, 
            i.each(this.segments, function(t) {
                t.update({
                    outerRadius: this.outerRadius,
                    innerRadius: this.outerRadius / 100 * this.options.percentageInnerCutout
                });
            }, this);
        },
        draw: function(t) {
            var e = t ? t : 1;
            this.clear(), i.each(this.segments, function(t, i) {
                t.transition({
                    circumference: this.calculateCircumference(t.value),
                    outerRadius: this.outerRadius,
                    innerRadius: this.outerRadius / 100 * this.options.percentageInnerCutout
                }, e), t.endAngle = t.startAngle + t.circumference, t.draw(), 0 === i && (t.startAngle = 1.5 * Math.PI), 
                i < this.segments.length - 1 && (this.segments[i + 1].startAngle = t.endAngle);
            }, this);
        }
    }), e.types.Doughnut.extend({
        name: "Pie",
        defaults: i.merge(s, {
            percentageInnerCutout: 0
        })
    });
}).call(this);