# -*- coding: utf-8 -*-
from django.db import models
from lotteries.models.lotterytype import LotteryType
from mysite.channels.models import Channel

WEEK_CHOICES = (
    (1, '一'), (2, '二'), (3, '三'), (4, '四'), (5, '五'), (6, '六'), (7, '日')
)

# =====================
# Lottery model
# =====================
class Lottery(models.Model):
    title = models.CharField(max_length=200)
    code = models.CharField(max_length=200)
    sort = models.SmallIntegerField('Sort', default=0, blank=True)
    lotterytype = models.ForeignKey(LotteryType)
    issue_set = models.TextField()
    week_cycle = models.CommaSeparatedIntegerField(max_length=20, choices=WEEK_CHOICES)
    yearly_break_start = models.DateField('Yearly Break Start')
    yearly_break_end = models.DateField('Yearly Break End')
    min_commission_gap = models.DecimalField(decimal_places=3, max_digits=3)
    min_profit = models.DecimalField(decimal_places=3, max_digits=3)
    issue_rule = models.CharField(max_length=200)
    description = models.TextField()
    number_rule = models.TextField()
    channel = models.ForeignKey(Channel)
    created = models.DateTimeField(editable=False)

    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'lotteries'
