# -*- coding: utf-8 -*-
from django.db import models
from lotteries.models.lotterytype import LotteryType
from mysite.channels.models import Channel

# =====================
# Lottery model
# =====================
class Lottery(models.Model):
    title = models.CharField(max_length=200)
    code = models.CharField(max_length=200)
    sort = models.SmallIntegerField('Sort', default=0, blank=True)
    lotterytype = models.ForeignKey(LotteryType)
    issue_set = models.TextField()
    week_cycle = models.SmallIntegerField()
    yearly_break_start = models.DateField('Yearly Break Start')
    yearly_break_end = models.DateField('Yearly Break End')
    min_commission_gap = models.DecimalField(decimal_places=3, max_digits=3)
    min_profit = models.DecimalField(decimal_places=3, max_digits=3)
    issue_rule = models.CharField(max_length=200)
    description = models.TextField()
    number_rule = models.TextField()
    channel = models.ForeignKey(Channel)

    def __unicode__(self):
        return self.title

    class Meta:
        app_label = 'lotteries'
