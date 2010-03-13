from django.db import models
from django.contrib.auth.models import User
from mysite.channels.models import Channel
# =====================
# Lottery Type model
# =====================
class LotteryType(models.Model):
    title = models.CharField(max_length=200)
    
    def __unicode__(self):
        return self.title

# =====================
# Lottery model
# =====================
class Lottery(models.Model):
    title = models.CharField(max_length=200)
    code = models.CharField(max_length=200)
    sort = models.SmallIntegerField('Sort')
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

#=================
# Method Model
#=================

class Method(models.Model):
    title = models.CharField(max_length=200)
    lottery = models.ForeignKey(Lottery)
    function_name = models.CharField('Check Prized Function Name', max_length=20)
    init_lock_func = models.CharField('Init Lock Function Name', max_length=100)
    level_count = models.SmallIntegerField('Total Levels', max_length=1)
    no_count = models.TextField('Direct Cost')
    description = models.TextField()
    is_closed = models.BooleanField('Closed?')
    is_use_lock = models.BooleanField('Use Lock?')
    lock_table_name = models.CharField('Lock Table', max_length=30)
    max_lost = models.DecimalField(decimal_places=2, max_digits=14)
    total_price = models.DecimalField(decimal_places=4, max_digits=8)
    
#=================
# Issue Model
#=================

class Issue(models.Model):
    title = models.CharField(max_length=20)
    code = models.CharField(max_length=30)
    lottery = models.ForeignKey(Lottery,related_name='lottery_id')
    date = models.DateField()
    sale_start = models.DateTimeField()
    sale_end = models.DateTimeField()
    cancel_deadline = models.DateTimeField()
    official_time = models.DateTimeField()
    write_time = models.DateTimeField()
    write_user_id = models.ForeignKey(User,related_name='write_user_id')
    verify_time = models.DateTimeField()
    verify_user_id = models.ForeignKey(User,related_name='verify_user_id')
    status_code = models.SmallIntegerField('Prize Status')
    status_deduct = models.SmallIntegerField('Deduct Status')
    status_point = models.SmallIntegerField('Return Point Status')
    status_check_prize = models.SmallIntegerField('Check Prize Status')
    status_prize = models.SmallIntegerField('Send Prize Status')
    status_task_to_project = models.SmallIntegerField('Task to Project Status')
    status_is_synced = models.BooleanField('Synced to History?',False)
    
    
