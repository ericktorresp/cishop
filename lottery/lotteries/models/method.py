from django.db import models
from lotteries.models import Lottery, PrizeGroup
from django.contrib.auth.models import User

class Mode(models.Model):
    title = models.CharField(max_length=20)
    rate = models.DecimalField(decimal_places=2, max_digits=4)
    
    def __unicode__(self):
        return self.title
    class Meta:
        app_label = 'lotteries'

#=================
# Method Model
#=================

class Method(models.Model):
    title = models.CharField(max_length=200)
    lottery = models.ForeignKey(Lottery)
    parent = models.ForeignKey('self', blank=True, null=True, related_name='belongs_to_method')
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
    mode = models.ManyToManyField(Mode)
    usersets = models.ManyToManyField(User, through='UserMethodSet')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'
        
class UserMethodSet(models.Model):
    user = models.ForeignKey(User)
    method = models.ForeignKey(Method)
    prizegroup = models.ForeignKey(PrizeGroup)
    point = models.DecimalField(decimal_places=3, max_digits=4)
    limit_bonus = models.DecimalField(decimal_places=4, max_digits=14, default=0.0000)
    is_closed = models.BooleanField(default=False)
    
    def __unicode__(self):
        return self.user.username + '\'s'
    
    class Meta:
        app_label = 'lotteries'
