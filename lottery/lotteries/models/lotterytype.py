from django.db import models

# =====================
# Lottery Type model
# =====================
class LotteryType(models.Model):
    title = models.CharField(max_length=200)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'lotteries'