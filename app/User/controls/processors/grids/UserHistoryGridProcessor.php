<?php
namespace occ2\inventar\User\controls\grids;

use occ2\GridControl\IProcessor;
use occ2\GridControl\GridControl;
use Nette\Application\UI\Presenter;
use occ2\model\ILogger;

/**
 * UserHistoryGridProcessor
 *
 * @author Milan Onderka <milan_onderka@occ2.cz>
 * @version 1.0.0
 */
class UserHistoryGridProcessor implements IProcessor
{
    public function process(GridControl $grid, Presenter $presenter)
    {
        $grid->setLoadOptionsCallback(UserHistoryGrid::TYPE, function () {
            return [
                ""=>"base.shared.all",
                ILogger::INFO=>"base.logger.status.info",
                ILogger::SUCCESS=>"base.logger.status.success",
                ILogger::WARNING=>"base.logger.status.warning",
                ILogger::DANGER=>"base.logger.status.danger",
                ILogger::EXCEPTION=>"base.logger.status.exception"
            ];
        });
    }
}