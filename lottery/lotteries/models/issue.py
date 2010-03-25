from django.db import models
from lotteries.models.lottery import Lottery
from django.contrib.auth.models import User
    
#=================
# Issue Model
#=================

class Issue(models.Model):
    title = models.CharField(max_length=20)
    code = models.CharField(max_length=30)
    lottery = models.ForeignKey(Lottery, related_name='belong_to_lottery')
    date = models.DateField()
    sale_start = models.DateTimeField()
    sale_end = models.DateTimeField()
    cancel_deadline = models.DateTimeField()
    official_time = models.DateTimeField()
    write_time = models.DateTimeField(editable=False)
    write_user = models.ForeignKey(User, related_name='code_writer', editable=False)
    verify_time = models.DateTimeField(editable=False)
    verify_user = models.ForeignKey(User, related_name='code_verifier', editable=False)
    status_code = models.SmallIntegerField('Prize Status', editable=False)
    status_deduct = models.SmallIntegerField('Deduct Status', editable=False)
    status_point = models.SmallIntegerField('Return Point Status', editable=False)
    status_check_prize = models.SmallIntegerField('Check Prize Status', editable=False)
    status_prize = models.SmallIntegerField('Send Prize Status', editable=False)
    status_task_to_project = models.SmallIntegerField('Task to Project Status', editable=False)
    status_is_synced = models.BooleanField('Synced to History?', default=False, editable=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'

#================
# Issue History Model
#================
       
class IssueHistory(models.Model):
    lottery = models.ForeignKey(Lottery)
    code = models.CharField(max_length=30)
    issue = models.ForeignKey(Issue)
    missed = models.TextField()
    total_missed = models.TextField()
    series = models.TextField()
    total_series = models.TextField()
    
    def __unicode__(self):
        return self
    
    class Meta:
        app_label = 'lotteries'

#================
# Issue Error Model
#================

class IssueError(models.Model):
    
    
    def __unicode__(self):
        return self.title
    class Meta:
        app_label = 'lotteries'
