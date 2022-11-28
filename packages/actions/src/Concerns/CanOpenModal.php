<?php

namespace Filament\Actions\Concerns;

use Closure;
use Filament\Actions\Modal\Actions\Action;
use Filament\Actions\Modal\Actions\Action as ModalAction;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Contracts\View\View;

trait CanOpenModal
{
    /**
     * @var array<ModalAction> | Closure
     */
    protected array | Closure $extraModalActions = [];

    protected bool | Closure | null $isModalCentered = null;

    protected bool | Closure $isModalSlideOver = false;

    /**
     * @var array<ModalAction> | Closure | null
     */
    protected array | Closure | null $modalActions = null;

    protected ModalAction | Closure | null $modalCancelAction = null;

    protected ModalAction | Closure | null $modalSubmitAction = null;

    protected string | Closure | null $modalButtonLabel = null;

    protected View | Htmlable | Closure | null $modalContent = null;

    protected View | Htmlable | Closure | null $modalFooter = null;

    protected string| Htmlable | Closure | null $modalHeading = null;

    protected string| Htmlable | Closure | null $modalSubheading = null;

    protected string | Closure | null $modalWidth = null;

    public function centerModal(bool | Closure | null $condition = true): static
    {
        $this->isModalCentered = $condition;

        return $this;
    }

    public function slideOver(bool | Closure $condition = true): static
    {
        $this->isModalSlideOver = $condition;

        return $this;
    }

    /**
     * @param  array<ModalAction> | Closure | null  $actions
     */
    public function modalActions(array | Closure | null $actions = null): static
    {
        $this->modalActions = $actions;

        return $this;
    }

    /**
     * @param  array<ModalAction> | Closure  $actions
     */
    public function extraModalActions(array | Closure $actions): static
    {
        $this->extraModalActions = $actions;

        return $this;
    }

    public function modalSubmitAction(ModalAction | Closure | null $action = null): static
    {
        $this->modalSubmitAction = $action;

        return $this;
    }

    public function modalCancelAction(ModalAction | Closure | null $action = null): static
    {
        $this->modalCancelAction = $action;

        return $this;
    }

    public function modalButton(string | Closure | null $label = null): static
    {
        $this->modalButtonLabel = $label;

        return $this;
    }

    public function modalContent(View | Htmlable | Closure | null $content = null): static
    {
        $this->modalContent = $content;

        return $this;
    }

    public function modalFooter(View | Htmlable | Closure | null $footer = null): static
    {
        $this->modalFooter = $footer;

        return $this;
    }

    public function modalHeading(string | Htmlable | Closure | null $heading = null): static
    {
        $this->modalHeading = $heading;

        return $this;
    }

    public function modalSubheading(string | Htmlable | Closure | null $subheading = null): static
    {
        $this->modalSubheading = $subheading;

        return $this;
    }

    public function modalWidth(string | Closure | null $width = null): static
    {
        $this->modalWidth = $width;

        return $this;
    }

    abstract public function getLivewireCallActionName(): string;

    /**
     * @return array<Action>
     */
    public function getModalActions(): array
    {
        if ($this->isWizard()) {
            return [];
        }

        if ($this->modalActions !== null) {
            return $this->filterHiddenModalActions(
                $this->evaluate($this->modalActions),
            );
        }

        $actions = array_merge(
            [$this->getModalSubmitAction()],
            $this->getExtraModalActions(),
            [$this->getModalCancelAction()],
        );

        if ($this->isModalCentered()) {
            $actions = array_reverse($actions);
        }

        return $this->filterHiddenModalActions($actions);
    }

    /**
     * @param  array<ModalAction>  $actions
     * @return array<ModalAction>
     */
    protected function filterHiddenModalActions(array $actions): array
    {
        return array_filter(
            $actions,
            fn (ModalAction $action): bool => ! $action->isHidden(),
        );
    }

    public function getModalSubmitAction(): ModalAction
    {
        if ($this->modalSubmitAction) {
            return $this->evaluate($this->modalSubmitAction);
        }

        return static::makeModalAction('submit')
            ->label($this->getModalButtonLabel())
            ->submit($this->getLivewireCallActionName())
            ->color(match ($color = $this->getColor()) {
                'gray' => 'primary',
                default => $color,
            });
    }

    public function getModalCancelAction(): ModalAction
    {
        if ($this->modalCancelAction) {
            return $this->evaluate($this->modalCancelAction);
        }

        return static::makeModalAction('cancel')
            ->label(__('filament-actions::modal.actions.cancel.label'))
            ->cancel()
            ->color('gray');
    }

    /**
     * @return array<ModalAction>
     */
    public function getExtraModalActions(): array
    {
        return $this->evaluate($this->extraModalActions);
    }

    public function getModalButtonLabel(): string
    {
        return $this->evaluate($this->modalButtonLabel) ?? __('filament-actions::modal.actions.submit.label');
    }

    public function getModalContent(): View | Htmlable | null
    {
        return $this->evaluate($this->modalContent);
    }

    public function getModalFooter(): View | Htmlable | null
    {
        return $this->evaluate($this->modalFooter);
    }

    public function getModalHeading(): string | Htmlable
    {
        return $this->evaluate($this->modalHeading) ?? $this->getLabel();
    }

    public function getModalSubheading(): string | Htmlable | null
    {
        return $this->evaluate($this->modalSubheading);
    }

    public function getModalWidth(): string
    {
        return $this->evaluate($this->modalWidth) ?? '4xl';
    }

    public function isModalCentered(): bool
    {
        return $this->evaluate($this->isModalCentered) ?? in_array($this->getModalWidth(), ['xs', 'sm']);
    }

    public function isModalSlideOver(): bool
    {
        return $this->evaluate($this->isModalSlideOver);
    }

    /**
     * @param  array<string, mixed> | null  $arguments
     */
    protected function makeExtraModalAction(string $name, ?array $arguments = null): ModalAction
    {
        return static::makeModalAction($name)
            ->action($this->getLivewireCallActionName(), $arguments)
            ->color('gray');
    }

    public static function makeModalAction(string $name): ModalAction
    {
        return ModalAction::make($name);
    }
}
