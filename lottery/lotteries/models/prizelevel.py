from django.db import models
from django.contrib.auth.models import User
from lotteries.models import Lottery, PrizeGroup, Method

class PrizeLevel(models.Model):
    description = models.TextField()
    prizegroup = models.ForeignKey(PrizeGroup)
    method = models.ForeignKey(Method)
    level = models.SmallIntegerField(max_length=2)
    prize = models.DecimalField(decimal_places=2, max_digits=10)
    point = models.DecimalField(decimal_places=4, max_digits=4)
    is_closed = models.BooleanField(default=False)
    userset = models.ManyToManyField(User, through='UserPrizeLevel')

    def __unicode__(self):
        return self.level
    
    class Meta:
        app_label = 'lotteries'

class UserPrizeLevel(models.Model):
    user = models.ForeignKey(User)
    prizelevel = models.ForeignKey(PrizeLevel)
    is_closed = models.BooleanField(default=False)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'
