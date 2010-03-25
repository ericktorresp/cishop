from django.db import models
from records.models import Project

class ExpandCode(models.Model):
    project = models.ForeignKey(Project)
    level = models.SmallIntegerField(max_length=2, default=1)
    multiple = models.SmallIntegerField(max_length=2, default=1)
    prize = models.DecimalField(decimal_places=4, max_digits=14)
    expanded_code = models.TextField()
    updated = models.DateTimeField(auto_now=True)
    hashvar = models.CharField(max_length=32)

    def __unicode__(self):
        return str(self.level) + ' Level, Prize is ' + str(self.prize)
    
    class Meta:
        app_label = 'records'
