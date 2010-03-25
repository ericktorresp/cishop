from django.db import models
from mysite.lotteries.models import Issue
from records.models import Task, Project

class TaskDetail(models.Model):
    task = models.ForeignKey(Task)
    project = models.ForeignKey(Project)
    multiple = models.SmallIntegerField(max_length=6)
    issue = models.ForeignKey(Issue)
    status = models.SmallIntegerField(max_length=2, default=0, editable=False, choices=((0, 0), (1, 1), (2, 2)))

    def __unicode__(self):
        return self.task.title + ' ' + self.issue.title

    class Meta:
        app_label = 'records'
