from django.db import models
from lotteries.models.lottery import Lottery
from django.contrib.auth.models import User
    
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
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'