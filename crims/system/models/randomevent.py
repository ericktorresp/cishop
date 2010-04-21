from django.db import models
from filebrowser.fields import FileBrowseField

class RandomEvent(models.Model):
    title = models.CharField(max_length=200)
    photo = FileBrowseField(u'Photo', max_length=200, directory="random/title/", format="Image", extensions=['.jpg', '.gif', '.png'])
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]

class RandomEventQuestion(models.Model):
    title = models.CharField(max_length=200)
    created = models.DateTimeField(editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]
    
class RandomEventChoice(models.Model):
    randomeventquestion = models.ForeignKey(RandomEventQuestion)
    answer = models.CharField(max_length=100)
    photo = FileBrowseField(u'Photo', max_length=200, directory="random/choice/", format="Image", extensions=['.jpg', '.gif', '.png'])
    
    def __unicode__(self):
        return self.photo
    
    class Meta:
        app_label = 'system'
        ordering = ['id', ]
