from django.db import models
from filebrowser.fields import FileBrowseField
from django.contrib.auth.models import User

class Guard(models.Model):
    title = models.CharField(max_length=100)
    strength = models.SmallIntegerField()
    photo = FileBrowseField(u'Photo', max_length=200, directory="guard/", format="Image", extensions=['.jpg', '.gif', '.png'])
    price = models.IntegerField()
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    user_guard = models.ManyToManyField(User, through='UserGuard')
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'

class UserGuard(models.Model):
    user = models.ForeignKey(User)
    guard = models.ForeignKey(Guard)
    created = models.DateTimeField('Created', editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.user.username + "'s " + self.guard.title
    
    class Meta:
        db_table = 'user_guard'
        app_label = 'system'
