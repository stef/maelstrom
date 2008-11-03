"""
The MIT License

Copyright (c) 2008 Will Larson, Button Down Design, LLC.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
"""

__DESCRIPTION__ = "A library for drawing simple analytical graphs and charts, such as Sparklines."
__EXAMPLE__ = """from analytic_chart import *
import random

data = tuple( random.randrange(100,1000) for x in xrange(30))
opts = {'w':500,'h':200,'value_lines':(500,600,), 'fill_between_value_lines':True}
LineChart(data,opts).draw('linechart1.png')
BarChart(data,opts).draw('barchart1.png')
opts ={'w':500,'h':200,'percentile_lines':(0.25,0.75),'fill_between_percentile_lines':True}
LineChart(data,opts).draw('linechart2.png')
BarChart(data,opts).draw('barchart2.png')
opts['value_lines'] = (250,500,750)
opts['padding_between_bars'] = 2
BarChart(data,opts).draw('barchart3.png')
LineChart(data,opts).draw('linechart3.png')

"""


import Image, ImageFont, ImageDraw

class Chart(object):
    'A class for creating charts.'
    def __init__(self,data,opts={}):
        self.data = data
        self.heights_cache = None
        self.widths_cache = None
        self.max_cache = None
        self.min_cache = None
        defaults = {
            'background_color':(25,25,25,100),
            'line_color':(230,230,230,20),
            'percentile_line_color':(85,85,255,100),
            'percentile_fill_color':(85,85,255,100),
            'value_line_color':(199,199,255,100),
            'value_fill_color':(85,85,85,100),
            'fill_between_percentile_lines':False,
            'fill_between_value_lines':False,
            'scale_from':None,
            'scale_to':None,
            'value_lines':(),
            'percentile_lines':(),
            'ext':"PNG",
            'h':100,
            'w':100,
            'padding':{'top':10,'bottom':10,'left':10,'right':10},
            'extract_height': lambda x: x,
            'line_width':1,
            }
        # overwrite defaults with passed options
        for key in opts:
            defaults[key] = opts[key]
        # assign values to object
        for key in defaults:
            setattr(self, key, defaults[key])

    def height(self):
        return self.h - self.padding['top'] - self.padding['bottom']

    def width(self):
        return self.w - self.padding['left'] - self.padding['right']
        
    def max(self):
        if self.max_cache is None:
            self.max_cache = max(self.heights())
        return self.max_cache

    def min(self):
        if self.min_cache is None:
            self.min_cache = min(self.heights())
        return self.min_cache

    def scale(self, lst):
        "Scale a list of numbers to relative heights within Chart's size."
        max = self.max()
        h = self.height()
        p = self.padding['top']

        # A bit awkward due to 0 equaling false in Python.
        if self.scale_to is not None:
            top = self.scale_to
        else:
            top = max
        if self.scale_from is not None:
            bottom = self.scale_from
        else:
            bottom = self.min()

        range = abs(top - bottom)
        def do_scale(x):
            percentage = ((x-bottom)*1.0) / range
            return h - (h * percentage) + p
        return list(do_scale(x) for x in lst)


    def heights(self):
        if self.heights_cache is None:
            self.heights_cache = tuple(self.extract_height(x) for x in self.data)
        return self.heights_cache

    def extract_height(self,x):
        return x

    def widths(self):
        if self.widths_cache is None:
            l = len(self.data)
            sw = self.segment_width()
            lp = self.padding['left']
            self.widths_cache = tuple((x*sw)+lp for x in xrange(0,l,1))
        return self.widths_cache

    def scale_value_lines(self):
        'Scales and sorts any value lines.'
        scaled = self.scale(self.value_lines)
        scaled.sort()
        return scaled

    def scale_percentile_lines(self):
        'Scales and sorts any percentile lines.'
        scaled = self.scale(self.heights())
        scaled.sort()
        n = len(scaled)
        points = list(scaled[int(round(x*(n+1)))] for x in self.percentile_lines)
        points.sort()
        return points

    def segment_width(self):
        return (self.width() * 1.0) / (len(self.data) -1)

    def scale_points(self):
        return zip(self.widths(),self.scale(self.heights()))

    def fill_width(self, leftmost,rightmost):
        'Returns tuple of x coords of start and end of percentile and value fills.'
        return leftmost,rightmost
        
    def draw(self):
        """
        Draws the basic details of the chart. Including the background,
        fills between percentile and value lines, and the actual percentile
        and value lines themselves.

        Returns a 3-tuple in format (points, draw, img)
        """
        # Create variables used for drawing.
        points = self.scale_points()
        l = len(points)
        leftmost = points[0][0]
        rightmost = points[-1][0]
        line_width = self.line_width
        fill_leftmost, fill_rightmost = self.fill_width(leftmost,rightmost)
        percentile_lines = self.scale_percentile_lines()
        value_lines = self.scale_value_lines()

        # Create the new image.
        img = Image.new("RGBA",(self.w,self.h),self.background_color)
        draw = ImageDraw.Draw(img)

        fill_types = ((percentile_lines,self.fill_between_percentile_lines,self.percentile_fill_color),
                      (value_lines, self.fill_between_value_lines,self.value_fill_color))
        for heights, should_fill, color in fill_types:
            if should_fill and len(heights) > 0:
                draw.rectangle((fill_leftmost,heights[0],fill_rightmost,heights[-1]),fill=color)
                   
        # Draw percentile and absolute value lines.
        line_types = ((percentile_lines,self.percentile_line_color), 
                      (value_lines,self.value_line_color))
        for heights, color in line_types:
            for height in heights:
                start = (leftmost, height)
                end = (rightmost, height)
                draw.line((start,end),fill=color,width=line_width)
        return points, draw, img

    def finish(self, img, filepath):
        if filepath is not None:
            return img.save(filepath,self.ext)
        else:
            return img


class LineChart(Chart):
    'A chart that represents its data with a line.'
    def draw(self,filepath=None):
        "Draw a line chart representation of chart's data."
        line_width = self.line_width
        points,draw,img = super(LineChart,self).draw()
        draw.line(points,fill=self.line_color,width=self.line_width)
        del draw
        return self.finish(img,filepath)


class BarChart(Chart):
    'A chart that represents its data with bars.'
    def __init__(self, data, opts={}):
        self.marking_padding = 5
        self.padding_between_bars = 5
        if not opts.has_key('scale_from'):
            opts['scale_from'] = 0
        super(BarChart,self).__init__(data,opts)

    def segment_width(self):
        l = len(self.data)
        w = self.width()
        return ((w*1.0)-((l-1)*self.padding_between_bars))/l

    def scale_width(self):
        l = len(self.data)
        sw = self.segment_width()
        pbb = self.padding_between_bars
        return tuple((x*sw)+(x*pbb) for x in xrange(l))

    def widths(self):
        if self.widths_cache is None:
            l = len(self.data)
            sw = self.segment_width()
            lp = self.padding['left']
            pbb = self.padding_between_bars
            self.widths_cache = tuple((x*sw)+(x*pbb) +lp for x in xrange(l))
        return self.widths_cache

    def fill_width(self, leftmost,rightmost):
        'Returns tuple of x coords of start and end of percentile and value fills.'
        l =  leftmost-self.marking_padding
        r= rightmost + self.segment_width() + self.marking_padding
        return l,r


    def draw(self,filepath=None):
        "Draw a bar chart representation of chart's data."
        points,draw,img = super(BarChart,self).draw()

        sw = self.segment_width()
        tp = self.padding['top']
        h = self.height()
        color = self.line_color
        for point in points:
            one = (point[0],point[1])
            x2 = point[0] + sw
            y2 = h
            two = (x2,y2)
            draw.rectangle((one,two), fill=color)
        
        del draw
        return self.finish(img,filepath)
