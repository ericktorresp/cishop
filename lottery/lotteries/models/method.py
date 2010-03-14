from django.db import models
from lotteries.models.lottery import Lottery
from django.contrib.auth.models import User
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
    usersets = models.ManyToManyField(User)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'
