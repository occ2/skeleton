ACL testing on model facades and presenters
notice:
be careful that all methods that can be tested automatically MUST NOT be public (must be private or protected).
If you need use public method you must run $this->acl(__FUNCTION__,$data) on begin of method
example:
@acl (resource=AclResource, privilege=read)

available @acl variables
loggedIn - user must be logged in
resource - \Nette\Security\Permission resource
privilege - \Nette\Security\Permission privilege (default read)
exception - exception class (default PermissionException) (use in facades)
redirect - redirect path (use in presenters)
message - exception or flash error message
code - exception error code
eventClass - failure event class
event - failure event anchor

presenters
@title title of action