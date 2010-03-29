# -*- coding: utf-8 -*-
from django.db import models
from lotteries.models import Lottery, Issue
from django.contrib.auth.models import User

#================
# Issue Error Model
# 错误类型                1:官方提前开奖[撤销派奖 + 系统撤单]; 2:录入号码错误[撤销派奖 + 重新判断中奖 + 重新派奖]; 3:官方未开奖
# 号码状态                0:未写入;1:写入待验证;2:已验证
# 扣款状态                0:未完成;1:进行中;2:已完成
# 返点状态                0:未开始;1:进行中;2:已完成
# 检查中奖状态      0:未开始;1:进行中;2:已完成
# 返奖状态                0:未开始;1:进行中;2:已完成
# 追号单转注单状态    0:未开始;1:进行中;2:已经完成)
# ...
# 撤销派奖状态(new_status_cancelbonus)    0:未开始, 1=进行中, 2=已完成, 9=被忽略
#================
ERROR_TYPE = (
              (1, u'Official ahead open'),
              (2, u'Error code'),
              (3, u'Didn\'t official open'),
              )
STATUS_CODE = (
              (0, u'Didn\'t Note'),
              (1, u'Noted,pending prove'),
              (2, u'Noted & proved'),
              )
STATUS_DEDUCT = (
                 (0, u'Not finished'),
                 (1, u'In progress'),
                 (2, u'Finished'),
                 )
STATUS = (
         (0, u'Not started'),
         (1, u'In progress'),
         (2, u'Finished'),
         )
STATUS_CANCELBONUS = STATUS + ((9, u'Ignored'),)

class IssueError(models.Model):
    lottery = models.ForeignKey(Lottery)
    issue = models.ForeignKey(Issue)
    error_type = models.SmallIntegerField(max_length=2, choices=ERROR_TYPE)
    ahead_open_time = models.DateTimeField(blank=True, null=True)
    note_time = models.DateTimeField(auto_now_add=True, editable=False)
    noter = models.ForeignKey(User, related_name='noter')
    old_code = models.CharField(max_length=30)
    old_status_code = models.SmallIntegerField(max_length=2, choices=STATUS_CODE)
    old_status_deduct = models.SmallIntegerField(max_length=2, choices=STATUS_DEDUCT)
    old_status_userpoint = models.SmallIntegerField(max_length=2, choices=STATUS)
    old_status_checkbonus = models.SmallIntegerField(max_length=2, choices=STATUS)
    old_status_bonus = models.SmallIntegerField(max_length=2, choices=STATUS)
    old_status_tasktoproject = models.SmallIntegerField(max_length=2, choices=STATUS)
    new_code = models.CharField(max_length=30)
    new_status_code = models.SmallIntegerField(max_length=2, choices=STATUS_CODE)
    new_status_deduct = models.SmallIntegerField(max_length=2, choices=STATUS_DEDUCT)
    new_status_userpoint = models.SmallIntegerField(max_length=2, choices=STATUS)
    new_status_checkbonus = models.SmallIntegerField(max_length=2, choices=STATUS)
    new_status_bonus = models.SmallIntegerField(max_length=2, choices=STATUS)
    new_status_tasktoproject = models.BooleanField(default=False)
    new_status_cancelbonus = models.SmallIntegerField(max_length=2, choices=STATUS_CANCELBONUS)
    new_status_repeal = models.SmallIntegerField(max_length=2, choices=STATUS_CANCELBONUS)
    
    def __unicode__(self):
        return self.lottery.title
    class Meta:
        app_label = 'lotteries'
