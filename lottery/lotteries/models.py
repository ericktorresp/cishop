from django.db import models

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
    min_commission_gap = models.FloatField()
    min_profit = models.FloatField()
    issue_rule = models.CharField(max_length=200)
    description = models.TextField()
    number_rule = models.TextField()

    def __unicode__(self):
        return self.title

