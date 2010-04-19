from django.db import models
from filebrowser.fields import FileBrowseField
from system.models.drug import Drug

class Building(models.Model):
    title = models.CharField(max_length=100)
    output = models.IntegerField()
    expend = models.IntegerField()
    price = models.IntegerField()
    photo = FileBrowseField(u'Photo', max_length=200, directory="building/", format="Image", extensions=['.jpg', '.gif', '.png'])
    drug = models.ForeignKey(Drug)
    created = models.DateTimeField('Created at', editable=False, auto_now_add=True)
    
    def __unicode__(self):
        return self.title
    
    class Meta:
        app_label = 'system'
