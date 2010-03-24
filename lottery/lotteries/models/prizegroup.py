from django.db import models
from django.contrib.auth.models import User
from lotteries.models import Lottery

class PrizeGroup(models.Model):
    title = models.CharField(max_length=100)
    lottery = models.ForeignKey(Lottery)
    status = models.SmallIntegerField(max_length=1, choices=((0, 0), (1, 1)))
    usersets = models.ManyToManyField(User, through='UserPrizeGroup')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'
        
class UserPrizeGroup(models.Model):
    title = models.CharField(max_length=30)
    user = models.ForeignKey(User)
    prizegroup = models.ForeignKey(PrizeGroup)
    lottery = models.ForeignKey(Lottery)
    is_disabled = models.BooleanField(default=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'
