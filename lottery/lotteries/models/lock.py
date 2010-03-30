# -*- coding: utf-8 -*-
from django.db import models
from lotteries.models.lottery import Lottery
from django.contrib.auth.models import User

class Lock(models.Model):
    title = models.CharField(max_length=100, db_index=True, help_text=u'封锁表名称')
    lottery = models.ForeignKey(Lottery, help_text=u'游戏名称')
    max_lost = models.DecimalField(decimal_places=4, max_digits=14, help_text=u'最大封锁值')
    code_function = models.CharField(max_length=100, help_text='确认中奖号码的函数')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'
