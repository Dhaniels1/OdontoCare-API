using Microsoft.AspNetCore.Authorization;
using Microsoft.AspNetCore.Mvc;
using OdontoCare.Application.Common;
using OdontoCare.Application.DTOs;
using OdontoCare.Application.Interfaces;
using OdontoCare.Application.Security;

namespace OdontoCare.API.Controllers;

[ApiController]
[Authorize(Roles = Roles.Todos)]
[Route("api/equipamentos")]
public sealed class EquipamentosController(IEquipamentoService service) : ControllerBase
{
    [HttpGet] public async Task<IActionResult> Listar() => Ok(ResultWrapper<List<EquipamentoDto>>.Ok(await service.ListarAsync()));
    [HttpGet("{id:int}")]
    public async Task<IActionResult> Buscar(int id) => await service.BuscarAsync(id) is { } e
        ? Ok(ResultWrapper<EquipamentoDto>.Ok(e)) : NotFound(ResultWrapper<object>.NotFound("Equipamento não encontrado."));
    [Authorize(Roles = Roles.Gestao)]
    [HttpPost]
    public async Task<IActionResult> Criar([FromBody] EquipamentoGravarDto dto)
    {
        var e = await service.CriarAsync(dto);
        return CreatedAtAction(nameof(Buscar), new { id = e.IdEquipamento }, ResultWrapper<EquipamentoDto>.Ok(e, "Equipamento cadastrado."));
    }
    [Authorize(Roles = Roles.Gestao)]
    [HttpPut("{id:int}")]
    public async Task<IActionResult> Atualizar(int id, [FromBody] EquipamentoGravarDto dto) => await service.AtualizarAsync(id, dto) is { } e
        ? Ok(ResultWrapper<EquipamentoDto>.Ok(e, "Equipamento atualizado.")) : NotFound(ResultWrapper<object>.NotFound());
    [Authorize(Roles = Roles.Administrador)]
    [HttpDelete("{id:int}")]
    public async Task<IActionResult> Excluir(int id) => await service.ExcluirAsync(id)
        ? Ok(ResultWrapper<object>.Ok(null, "Equipamento excluído.")) : NotFound(ResultWrapper<object>.NotFound());
}
