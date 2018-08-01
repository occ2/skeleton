@currentUser - if set test is passed if $userId is current user id
@aclResource - \Nette\Security\Permission resource
@aclPrivilege - \Nette\Security\Permission privilege (default read)
@aclExceptionClass - exception class (default \Exception)
@aclExceptionMessage - exception error message
@aclExceptionCode - exception error code
@aclEventClass - exception event class
@aclEventAnchor - exception event anchor

Calling ACL testing:

function _acl(__FUNCTION__,array $data=[],int $userId=null)
if passed return true, if not passed throw exception defined in @aclExceptionClass

