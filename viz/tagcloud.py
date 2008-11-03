#!/usr/bin/env python

import math
from string import punctuation
import Image 
import ImageDraw 
import ImageFont 

# See also, for the power function to get tag classes:
# http://thraxil.com/users/anders/posts/2005/12/13/scaling-tag-clouds/
# and
# http://behemoth.ccnmtl.columbia.edu/test/clouds/cloud.txt

# For stop words:
# http://www.dcs.gla.ac.uk/idom/ir_resources/linguistic_utils/stop_words

stop_words = """a about above across after afterwards again against all 
almost alone along already also although always am among amongst amoungst 
amount an and another any anyhow anyone anything anyway anywhere are 
around as at back be became because become becomes becoming been before
beforehand behind being below beside besides between beyond bill both
bottom but by call can cannot cant co computer con could couldnt cry
de describe detail do done down due during each eg eight either eleven
else elsewhere empty enough etc even ever every everyone everything
everywhere except few fifteen fify fill find fire first five for
former formerly forty found four from front full further get give go
had has hasnt have he hence her here hereafter hereby herein hereupon
hers herself him himself his how however hundred i ie if in inc indeed
interest into is it its itself keep last latter latterly least less
ltd made many may me meanwhile might mill mine more moreover most
mostly move much must my myself name namely neither never nevertheless
next nine no nobody none noone nor not nothing now nowhere of off
often on once one only onto or other others otherwise our ours
ourselves out over own part per perhaps please put rather re same see
seem seemed seeming seems serious several she should show side since
sincere six sixty so some somehow someone something sometime sometimes
somewhere still such system take ten than that the their them
themselves then thence there thereafter thereby therefore therein
thereupon these they thick thin third this those though three through
throughout thru thus to together too top toward towards twelve twenty
two un under until up upon us very via was we well were what whatever
when whence whenever where whereafter whereas whereby wherein
whereupon wherever whether which while whither who whoever whole whom
whose why will with within without would yet you your yours yourself
yourselves"""

stop_words = stop_words.split()

# For more stop words:
# http://dev.mysql.com/tech-resources/articles/full-text-revealed.html

more_stop_words = """a's, able, about, above, according, accordingly, 
across, actually, after, afterwards, again, against, ain't, all, 
allow, allows, almost, alone, along, already, also, although, always, 
am, among, amongst, an, and, another, any, anybody, anyhow, anyone, 
anything, anyway, anyways, anywhere, apart, appear, appreciate, 
appropriate, are, aren't, around, as, aside, ask, asking, associated, 
at, available, away, awfully, be, became, because, become, becomes, 
becoming, been, before, beforehand, behind, being, believe, below, 
beside, besides, best, better, between, beyond, both, brief, but, by, 
c'mon, c's, came, can, can't, cannot, cant, cause, causes, certain, 
certainly, changes, clearly, co, com, come, comes, concerning, 
consequently, consider, considering, contain, containing, contains, 
corresponding, could, couldn't, course, currently, definitely, 
described, despite, did, didn't, different, do, does, doesn't, doing, 
don't, done, down, downwards, during, each, edu, eg, eight, either, 
else, elsewhere, enough, entirely, especially, et, etc, even, ever, 
every, everybody, everyone, everything, everywhere, ex, exactly, 
example, except, far, few, fifth, first, five, followed, following, 
follows, for, former, formerly, forth, four, from, further, 
furthermore, get, gets, getting, given, gives, go, goes, going, gone, 
got, gotten, greetings, had, hadn't, happens, hardly, has, hasn't, 
have, haven't, having, he, he's, hello, help, hence, her, here, 
here's, hereafter, hereby, herein, hereupon, hers, herself, hi, him, 
himself, his, hither, hopefully, how, howbeit, however, i'd, i'll, 
i'm, i've, ie, if, ignored, immediate, in, inasmuch, inc, indeed, 
indicate, indicated, indicates, inner, insofar, instead, into, 
inward, is, isn't, it, it'd, it'll, it's, its, itself, just, keep, 
keeps, kept, know, knows, known, last, lately, later, latter, 
latterly, least, less, lest, let, let's, like, liked, likely, little, 
look, looking, looks, ltd, mainly, many, may, maybe, me, mean, 
meanwhile, merely, might, more, moreover, most, mostly, much, must, 
my, myself, name, namely, nd, near, nearly, necessary, need, needs, 
neither, never, nevertheless, new, next, nine, no, nobody, non, none, 
noone, nor, normally, not, nothing, novel, now, nowhere, obviously, 
of, off, often, oh, ok, okay, old, on, once, one, ones, only, onto, 
or, other, others, otherwise, ought, our, ours, ourselves, out, 
outside, over, overall, own, particular, particularly, per, perhaps, 
placed, please, plus, possible, presumably, probably, provides, que, 
quite, qv, rather, rd, re, really, reasonably, regarding, regardless, 
regards, relatively, respectively, right, said, same, saw, say, 
saying, says, second, secondly, see, seeing, seem, seemed, seeming, 
seems, seen, self, selves, sensible, sent, serious, seriously, seven, 
several, shall, she, should, shouldn't, since, six, so, some, 
somebody, somehow, someone, something, sometime, sometimes, somewhat, 
somewhere, soon, sorry, specified, specify, specifying, still, sub, 
such, sup, sure, t's, take, taken, tell, tends, th, than, thank, 
thanks, thanx, that, that's, thats, the, their, theirs, them, 
themselves, then, thence, there, there's, thereafter, thereby, 
therefore, therein, theres, thereupon, these, they, they'd, they'll, 
they're, they've, think, third, this, thorough, thoroughly, those, 
though, three, through, throughout, thru, thus, to, together, too, 
took, toward, towards, tried, tries, truly, try, trying, twice, two, 
un, under, unfortunately, unless, unlikely, until, unto, up, upon, 
us, use, used, useful, uses, using, usually, value, various, very, 
via, viz, vs, want, wants, was, wasn't, way, we, we'd, we'll, we're, 
we've, welcome, well, went, were, weren't, what, what's, whatever, 
when, whence, whenever, where, where's, whereafter, whereas, whereby, 
wherein, whereupon, wherever, whether, which, while, whither, who, 
who's, whoever, whole, whom, whose, why, will, willing, wish, with, 
within, without, won't, wonder, would, would, wouldn't, yes, yet, 
you, you'd, you'll, you're, you've, your, yours, yourself, 
yourselves, zero"""

more_stop_words = more_stop_words.split(', ')

stop_words = stop_words + filter(lambda x:x not in stop_words, more_stop_words)

# For common words:
#
# http://www.uri.edu/comm_service/cued_speech/amerpron.html
#
# which says about the source fo the 1000 or so words:
#
#     compiled mostly from words in a special dictionary by Robert 
#     Shaw, The New Horizon xt').read().split()

excluded_words = stop_words + filter(lambda x:x not in stop_words, common_words)

width = 450
height = width

image_left_margin = 5
image_right_margin = 5

# Find fonts on your system and set this path:
fontPath="/usr/share/fonts/truetype/msttcorefonts/arial.ttf"

arial18 = ImageFont.truetype(fontPath,18) 
arial28 = ImageFont.truetype(fontPath,28) 
arial36 = ImageFont.truetype(fontPath,36) 
arial48 = ImageFont.truetype(fontPath,48) 
arial64 = ImageFont.truetype(fontPath,64) 

font_classes = [arial18, arial28, arial36, arial48, arial64]

words_and_counts = {}

for line in open('geojeff_twitter.txt').readlines():
    line = line.strip()
    possible_start_times = ['%02d:' % num for num in range(24)]
    for possible_start_time in possible_start_times:
        if possible_start_time in line:
            line = line[:line.find(possible_start_time)]
    words = line.split()
    for word in words:
        word = word.strip(punctuation)
        if len(word) > 0:
            if word.endswith('\'s'):
                word = word[:-2]
            exclude = False
            try:
                number = int(word)
            except (ValueError, IndexError):
                number = None
            if number:
                exclude = True
            elif 'http' in word:
                exclude = True
            elif len(word) == 1:
                exclude = True
            if not exclude:
                word = word.lower()
                if word not in excluded_words:
                    words_and_counts[word] \
                        = words_and_counts.get(word, 0) + 1

tags = words_and_counts.items()
tags.sort()

levels = 5

def ex_weights(l): return [int(w) for (t,w) in l]

max_weight = max(ex_weights(tags))
min_weight = min(ex_weights(tags))

thresholds = [math.pow(max_weight - min_weight + 1,float(i) \
                / float(levels)) for i in range(0,levels)]

def class_from_weight(w,thresholds):
    i = 0
    for t in thresholds:
        i += 1
        if w <= t:
            return i
    return i

im = Image.new("RGB",(width,height),"#ddd") 
draw = ImageDraw.Draw(im) 

textsizes = [draw.textsize('Boy', font_classes[0]),
             draw.textsize('Boy', font_classes[1]),
             draw.textsize('Boy', font_classes[2]),
             draw.textsize('Boy', font_classes[3]),
             draw.textsize('Boy', font_classes[4])]

x = 0
# fix width to at least fit the longest item
for (t,w) in tags:
    c = class_from_weight(w,thresholds) - 1
    font = font_classes[c]
    word_w,word_h = draw.textsize(t, font=font)
    if (x + word_w) > width:
        width = x + word_w

lines = []
current_line = []
x = 0
y = 0
word_left_margin = 0
max_font_index = 0
for (t,w) in tags:
    c = class_from_weight(w,thresholds) - 1
    font = font_classes[c]
    word_w,word_h = draw.textsize(t, font=font)

    # set the x position
    if len(current_line) == 0:
        x = image_left_margin
        word_left_margin = 0
    else:
        previous_word_w,previous_word_h = current_line[-1][3]
        word_left_margin = int((float(previous_word_w) * 0.05 \
                                   + float(word_w) * 0.05) / 2.0)
        x = x + previous_word_w + word_left_margin

    if (x + word_w) < width:
        current_line.append((t, c, (x,y), (word_w,word_h), 
                             font, word_left_margin))
        if c > max_font_index:
            max_font_index = c
    else:
        lines.append(current_line)
        y = y + textsizes[max_font_index][1]
        max_font_index = 0
        current_line = []
        x = image_left_margin
        word_left_margin = 0
        current_line.append((t, c, (x,y), (word_w,word_h), 
                             font, word_left_margin))
        if c > max_font_index:
            max_font_index = c

image_width = 0
image_height = 0
lines_adjusted = []
for line in lines:
    width_for_line = 0
    max_height_for_line = 0
    for word_data in line:
        x,y = word_data[2]
        if x > width_for_line:
            width_for_line = x
        word_left_margin = word_data[5]
        word_w,word_h = word_data[3]
        width_for_line += word_left_margin + word_w
        if word_h > max_height_for_line:
            max_height_for_line = word_h
    line_adjusted = []
    for word_data in line:
        word_w,word_h = word_data[3]
        if word_h != max_height_for_line:
            x,y = word_data[2]
            y_adjusted = y + int((float(max_height_for_line) \
                                    - float(word_h)) * 0.5)
            line_adjusted.append((word_data[0], word_data[1], 
                                  (x,y_adjusted), word_data[3], 
                                  word_data[4], word_data[5]))
        else:
            line_adjusted.append(word_data)
    lines_adjusted.append(line_adjusted)
    if image_width < width_for_line:
        image_width = width_for_line
    image_height += max_height_for_line

im = Image.new("RGB",(image_width,image_height),"#ddd") 
draw = ImageDraw.Draw(im) 
for line in lines_adjusted:
    for word_data in line:
        word = word_data[0]
        x,y = word_data[2]
        font = word_data[4]
        draw.text((x,y),word,font=font,fill="black") 

im.save("tagcloud.png") 

for line in lines_adjusted:
    for word_data in line:
        print word_data[0]

