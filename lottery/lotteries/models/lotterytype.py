from django.db import models
from mysite.channels.models import Channel

# =====================
# Lottery Type model
# =====================
class LotteryType(models.Model):
    title = models.CharField(max_length=200)
    channel = models.ForeignKey(Channel)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'